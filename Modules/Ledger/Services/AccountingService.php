<?php

namespace Modules\Ledger\Services;

use Illuminate\Support\Facades\DB;
use Modules\Ledger\Enums\LedgerStatus;
use Modules\Ledger\Repositories\Interfaces\LedgerRepositoryInterface;
use Modules\Ledger\Services\JournalService;

class AccountingService
{
    public function __construct(
        protected LedgerRepositoryInterface $ledgerRepository,
        protected JournalService $journalService,
    ) {
    }

    public function createTransaction(array $payload, array $entries): object
    {
        if (!$this->ledgerRepository->getBalancedEntries($entries)) {
            throw new \InvalidArgumentException('Ledger entries must balance before posting.');
        }

        return DB::transaction(function () use ($payload, $entries) {
            $transactionPayload = array_merge($payload, [
                'total_debit' => array_sum(array_map(fn ($entry) => (float) ($entry['debit'] ?? 0), $entries)),
                'total_credit' => array_sum(array_map(fn ($entry) => (float) ($entry['credit'] ?? 0), $entries)),
                'status' => LedgerStatus::COMPLETED->value,
                'posted_at' => now(),
            ]);

            $transaction = $this->ledgerRepository->createTransaction($transactionPayload);

            foreach ($entries as $entry) {
                $entryPayload = array_merge($entry, [
                    'ledger_transaction_id' => $transaction->id,
                ]);

                $this->ledgerRepository->createEntry($entryPayload);
            }

            $this->journalService->recordJournalEntry([
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'ledger_transaction_id' => $transaction->id,
                'description' => $payload['description'] ?? 'Posted ledger transaction',
                'posted_at' => now(),
                'metadata' => $payload['metadata'] ?? [],
            ]);

            return $this->ledgerRepository->findTransaction($transaction->id);
        });
    }

    public function reverseTransaction(object $transaction, ?string $reason = null): object
    {
        if ($transaction->status === LedgerStatus::REVERSED->value) {
            throw new \LogicException('Transaction has already been reversed.');
        }

        $entries = $transaction->entries->map(fn ($entry) => [
            'account_id' => $entry->account_id,
            'debit' => $entry->credit,
            'credit' => $entry->debit,
            'description' => 'Reversal of entry '.$entry->id,
            'metadata' => ['reversal_of_entry_id' => $entry->id],
        ])->all();

        return DB::transaction(function () use ($transaction, $entries, $reason) {
            $reversalPayload = [
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => $transaction->type,
                'description' => 'Reversal of transaction '.$transaction->uuid,
                'metadata' => array_merge($transaction->metadata ?? [], ['reversal_reason' => $reason]),
                'status' => LedgerStatus::REVERSED->value,
                'posted_at' => now(),
                'reversed_at' => now(),
            ];

            $reversalTransaction = $this->ledgerRepository->createTransaction($reversalPayload);

            foreach ($entries as $entryPayload) {
                $entryPayload['ledger_transaction_id'] = $reversalTransaction->id;
                $this->ledgerRepository->createEntry($entryPayload);
            }

            $this->journalService->recordJournalEntry([
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'ledger_transaction_id' => $reversalTransaction->id,
                'description' => 'Posted reversal transaction',
                'posted_at' => now(),
                'metadata' => ['original_transaction_id' => $transaction->id],
            ]);

            $transaction->update(['status' => LedgerStatus::REVERSED->value, 'reversed_at' => now()]);

            return $this->ledgerRepository->findTransaction($reversalTransaction->id);
        });
    }
}
