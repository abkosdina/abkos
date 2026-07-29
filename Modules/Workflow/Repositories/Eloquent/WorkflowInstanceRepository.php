<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Repositories\Interfaces\WorkflowInstanceRepositoryInterface;

class WorkflowInstanceRepository implements WorkflowInstanceRepositoryInterface
{
    public function create(array $data): object
    {
        return WorkflowInstance::query()->create($data);
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowInstance::query()->find($id, $columns);
    }

    public function update(int|string $id, array $data): bool
    {
        return WorkflowInstance::query()->findOrFail($id)->update($data);
    }

    public function getByEntity(string $entityType, int|string $entityId): array
    {
        return WorkflowInstance::query()->where('entity_type', $entityType)->where('entity_id', $entityId)->get()->all();
    }

    public function getActiveInstancesByStatus(string $status): array
    {
        return WorkflowInstance::query()->where('status', $status)->get()->all();
    }
}
