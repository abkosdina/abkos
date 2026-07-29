<?php

namespace Modules\Wallet\Repositories\Eloquent;

use Modules\Wallet\Models\FinancialTransaction;
use Modules\Wallet\Repositories\Interfaces\FinancialTransactionRepositoryInterface;

class FinancialTransactionRepository implements FinancialTransactionRepositoryInterface
{
    public function __construct(protected FinancialTransaction $model)
    {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->query()->with(['senderWallet', 'receiverWallet', 'walletTransactions', 'ledgerTransaction'])->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()->with(['senderWallet', 'receiverWallet', 'walletTransactions', 'ledgerTransaction'])->find($id, $columns);
    }

    public function findByIdempotencyKey(string $idempotencyKey, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('idempotency_key', $idempotencyKey)->with(['senderWallet', 'receiverWallet', 'walletTransactions', 'ledgerTransaction'])->first($columns);
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }
}
