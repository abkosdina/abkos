<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function findByUuid(string $uuid, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function getActiveByModule(string $module): array;
}
