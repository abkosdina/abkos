<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowVersion;
use Modules\Workflow\Repositories\Interfaces\WorkflowVersionRepositoryInterface;

class WorkflowVersionRepository implements WorkflowVersionRepositoryInterface
{
    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowVersion::query()->find($id, $columns);
    }

    public function create(array $data): object
    {
        return WorkflowVersion::query()->create($data);
    }

    public function activateVersion(int|string $id): bool
    {
        $workflowVersion = WorkflowVersion::query()->findOrFail($id);
        return $workflowVersion->update(['is_active' => true]);
    }

    public function getVersionsForDefinition(int|string $workflowDefinitionId): array
    {
        return WorkflowVersion::query()->where('workflow_definition_id', $workflowDefinitionId)->get()->all();
    }
}
