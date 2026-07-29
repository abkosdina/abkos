<?php

namespace Modules\Chat\Repositories\Interfaces;

interface ChatMessageReadRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function markAsRead(int|string $messageId, int|string $userId): object;

    public function getByMessageAndUser(int|string $messageId, int|string $userId, array $columns = ['*']): ?object;
}
