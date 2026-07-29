<?php

namespace Modules\Wallet\Repositories\Eloquent;

use Modules\Wallet\Models\WalletTransaction;
use Modules\Wallet\Repositories\Interfaces\WalletTransactionRepositoryInterface;

class WalletTransactionRepository implements WalletTransactionRepositoryInterface
{
    public function __construct(protected WalletTransaction $model)
    {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->query()->with('ledgerTransaction')->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()->with('ledgerTransaction')->find($id, $columns);
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }

    public function filterByWallet(int|string $walletId, array $filters = [], array $columns = ['*']): array
    {
        $query = $this->model->query()->where('wallet_id', $walletId);

        if (isset($filters['type'])) {
            $query->where('transaction_type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->with('ledgerTransaction')->get($columns)->all();
    }
}
