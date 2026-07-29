<?php

namespace Modules\Chat\Repositories\Interfaces;

interface ChatMessageRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function findByUuid(string $uuid, array $columns = ['*']): ?object;

    public function getByRoom(int|string $roomId, array $columns = ['*']): array;
}
