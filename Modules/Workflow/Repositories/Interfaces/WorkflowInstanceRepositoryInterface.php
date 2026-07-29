<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowInstanceRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function update(int|string $id, array $data): bool;

    public function getByEntity(string $entityType, int|string $entityId): array;

    public function getActiveInstancesByStatus(string $status): array;
}
