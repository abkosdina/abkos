<?php

namespace Modules\Chat\Repositories\Interfaces;

interface ChatRoomRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function findWithRelations(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function findByUuid(string $uuid, array $columns = ['*']): ?object;

    public function getForUser(int|string $userId): array;

    public function getArchivedForUser(int|string $userId): array;
}
