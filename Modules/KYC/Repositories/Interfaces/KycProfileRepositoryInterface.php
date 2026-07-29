<?php

namespace Modules\KYC\Repositories\Interfaces;

interface KycProfileRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function findByUserId(int|string $userId, array $columns = ['*']): ?object;
}
