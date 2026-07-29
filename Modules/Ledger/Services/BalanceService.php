<?php

namespace Modules\Ledger\Services;

use Modules\Ledger\Repositories\Interfaces\BalanceRepositoryInterface;
use Modules\Ledger\Repositories\Interfaces\LedgerRepositoryInterface;

class BalanceService
{
    public function __construct(
        protected LedgerRepositoryInterface $ledgerRepository,
        protected BalanceRepositoryInterface $balanceRepository,
    ) {
    }

    public function calculateBalances(int|string $accountId): object
    {
        $entries = $this->ledgerRepository->getEntriesByAccount($accountId);
        $balance = $this->balanceRepository->findByAccountId($accountId);

        $debit = array_sum(array_map(fn ($entry) => (float) ($entry->debit ?? 0), $entries));
        $credit = array_sum(array_map(fn ($entry) => (float) ($entry->credit ?? 0), $entries));

        $total = $credit - $debit;
        $available = $total;

        if (!$balance) {
            $currency = count($entries) > 0 ? $entries[0]->currency : 'USD';

            $balance = $this->balanceRepository->create([
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'account_id' => $accountId,
                'available_balance' => $available,
                'blocked_balance' => 0,
                'pending_balance' => 0,
                'total_balance' => $total,
                'currency' => $currency,
            ]);
        } else {
            $balance->update([
                'available_balance' => $available,
                'total_balance' => $total,
                'blocked_balance' => $balance->blocked_balance,
                'pending_balance' => $balance->pending_balance,
            ]);
        }

        // Do not treat account balance id as a financial period id.
        $this->createSnapshot($balance->account_id, null);

        return $balance->refresh();
    }

    public function updateRunningBalance(int|string $accountId, float $debit, float $credit): object
    {
        return $this->calculateBalances($accountId);
    }

    public function createSnapshot(int|string $accountId, ?int $periodId = null): object
    {
        $balance = $this->balanceRepository->findByAccountId($accountId);

        return $this->balanceRepository->snapshot([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'account_id' => $accountId,
            'period_id' => $periodId,
            'available_balance' => $balance->available_balance,
            'blocked_balance' => $balance->blocked_balance,
            'pending_balance' => $balance->pending_balance,
            'total_balance' => $balance->total_balance,
            'currency' => $balance->currency,
            'snapshot_date' => now()->toDateString(),
            'metadata' => ['source' => 'ledger_recalculation'],
        ]);
    }
}

