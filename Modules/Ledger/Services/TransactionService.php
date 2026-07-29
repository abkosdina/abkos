<?php

namespace Modules\Ledger\Services;

use Modules\Ledger\Repositories\Interfaces\AccountRepositoryInterface;
use Modules\Ledger\Repositories\Interfaces\LedgerRepositoryInterface;

class TransactionService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected BalanceService $balanceService,
        protected AccountRepositoryInterface $accountRepository,
        protected LedgerRepositoryInterface $ledgerRepository,
    ) {
    }

    public function listTransactions(): array
    {
        return $this->ledgerRepository->all();
    }

    public function findTransaction(int|string $id): ?object
    {
        return $this->ledgerRepository->findTransaction($id);
    }

    public function createTransaction(array $payload, array $entries): object
    {
        $transaction = $this->accountingService->createTransaction($payload, $entries);

        foreach ($transaction->entries as $entry) {
            $this->balanceService->updateRunningBalance($entry->account_id, (float) $entry->debit, (float) $entry->credit);
        }

        return $transaction;
    }

    public function reverseTransaction(object $transaction, ?string $reason = null): object
    {
        $reversal = $this->accountingService->reverseTransaction($transaction, $reason);

        foreach ($reversal->entries as $entry) {
            $this->balanceService->updateRunningBalance($entry->account_id, (float) $entry->debit, (float) $entry->credit);
        }

        return $reversal;
    }

    public function transferFunds(int|string $fromAccountId, int|string $toAccountId, float $amount, ?string $description = null, array $metadata = []): object
    {
        $fromAccount = $this->accountRepository->find($fromAccountId);
        $toAccount = $this->accountRepository->find($toAccountId);

        if (!$fromAccount || !$toAccount) {
            throw new \InvalidArgumentException('Both source and destination accounts must exist.');
        }

        $payload = [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'transfer',
            'description' => $description ?? sprintf('Transfer from account %s to account %s', $fromAccountId, $toAccountId),
            'metadata' => $metadata,
        ];

        $entries = [
            [
                'account_id' => $fromAccountId,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Transfer debit',
                'metadata' => ['transfer_to' => $toAccountId],
            ],
            [
                'account_id' => $toAccountId,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Transfer credit',
                'metadata' => ['transfer_from' => $fromAccountId],
            ],
        ];

        return $this->createTransaction($payload, $entries);
    }
}
