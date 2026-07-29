<?php

namespace Modules\Ledger\Services;

use Modules\Ledger\Repositories\Interfaces\AccountRepositoryInterface;
use Modules\Ledger\Services\BalanceService;
use Modules\Ledger\Services\TransactionService;

class LedgerService
{
    public function __construct(
        protected TransactionService $transactionService,
        protected AccountRepositoryInterface $accountRepository,
        protected BalanceService $balanceService,
    ) {
    }

    public function listAccounts(): array
    {
        return $this->accountRepository->all();
    }

    public function createAccount(array $data): object
    {
        $payload = array_merge($data, [
            'uuid' => $data['uuid'] ?? \Illuminate\Support\Str::uuid()->toString(),
        ]);

        $account = $this->accountRepository->create($payload);

        $this->balanceService->calculateBalances($account->id);

        return $account;
    }

    public function getAccount(int|string $id): ?object
    {
        return $this->accountRepository->find($id);
    }

    public function getAccountBalance(int|string $id): object
    {
        $account = $this->accountRepository->find($id);

        if (!$account) {
            throw new \InvalidArgumentException('Account not found.');
        }

        return $this->balanceService->calculateBalances($account->id);
    }

    public function listTransactions(): array
    {
        return $this->transactionService->listTransactions();
    }

    public function getTransaction(int|string $id): ?object
    {
        return $this->transactionService->findTransaction($id);
    }

    public function createTransaction(array $payload, array $entries): object
    {
        return $this->transactionService->createTransaction($payload, $entries);
    }

    public function reverseTransaction(int|string $id, ?string $reason = null): object
    {
        return $this->transactionService->reverseTransaction($this->transactionService->findTransaction($id), $reason);
    }

    public function transferFunds(int|string $fromAccountId, int|string $toAccountId, float $amount, ?string $description = null, array $metadata = []): object
    {
        return $this->transactionService->transferFunds($fromAccountId, $toAccountId, $amount, $description, $metadata);
    }
}
