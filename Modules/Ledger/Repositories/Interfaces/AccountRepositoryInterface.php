<?php

namespace Modules\Ledger\Repositories\Interfaces;

interface AccountRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function findByUuid(string $uuid, array $columns = ['*']): ?object;

    public function getByOwner(string $ownerType, int|string $ownerId): array;
}
