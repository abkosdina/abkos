<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowDefinition;
use Modules\Workflow\Repositories\Interfaces\WorkflowRepositoryInterface;

class WorkflowRepository implements WorkflowRepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return WorkflowDefinition::query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowDefinition::query()->find($id, $columns);
    }

    public function findByUuid(string $uuid, array $columns = ['*']): ?object
    {
        return WorkflowDefinition::query()->where('uuid', $uuid)->first($columns);
    }

    public function create(array $data): object
    {
        return WorkflowDefinition::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return WorkflowDefinition::query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return WorkflowDefinition::query()->findOrFail($id)->delete();
    }

    public function getActiveByModule(string $module): array
    {
        return WorkflowDefinition::query()->where('module', $module)->where('is_active', true)->get()->all();
    }
}
