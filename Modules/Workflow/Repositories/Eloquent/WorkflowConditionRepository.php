<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowCondition;
use Modules\Workflow\Repositories\Interfaces\WorkflowConditionRepositoryInterface;

class WorkflowConditionRepository implements WorkflowConditionRepositoryInterface
{
    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowCondition::query()->find($id, $columns);
    }

    public function getActiveByWorkflowDefinition(int|string $workflowDefinitionId): array
    {
        return WorkflowCondition::query()
            ->where('workflow_definition_id', $workflowDefinitionId)
            ->where('is_active', true)
            ->get()
            ->all();
    }

    public function create(array $data): object
    {
        return WorkflowCondition::query()->create($data);
    }
}
