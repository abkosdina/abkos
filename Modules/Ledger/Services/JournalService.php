<?php

namespace Modules\Ledger\Services;

use Modules\Ledger\Repositories\Interfaces\JournalRepositoryInterface;

class JournalService
{
    public function __construct(protected JournalRepositoryInterface $journalRepository)
    {
    }

    public function recordJournalEntry(array $data): object
    {
        return $this->journalRepository->create($data);
    }

    public function getEntriesByTransaction(int|string $transactionId): array
    {
        return $this->journalRepository->getByTransactionId($transactionId);
    }
}
