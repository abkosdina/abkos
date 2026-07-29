<?php

namespace Modules\Chat\Repositories\Interfaces;

interface ChatParticipantRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function getByRoom(int|string $roomId, array $columns = ['*']): array;

    public function getByUser(int|string $userId, array $columns = ['*']): array;
}
