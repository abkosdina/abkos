<?php

namespace Modules\Chat\Repositories\Interfaces;

interface ChatAttachmentRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function findByUuid(string $uuid, array $columns = ['*']): ?object;

    public function getByMessage(int|string $messageId, array $columns = ['*']): array;
}
