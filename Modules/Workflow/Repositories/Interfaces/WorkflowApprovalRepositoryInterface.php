<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowApprovalRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function findByInstanceAndStep(int|string $instanceId, int|string $stepId, int|string $approverId): ?object;

    public function getByInstance(int|string $instanceId): array;

    public function update(int|string $id, array $data): bool;
}
