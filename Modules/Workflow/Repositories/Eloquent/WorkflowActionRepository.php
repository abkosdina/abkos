<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowAction;
use Modules\Workflow\Repositories\Interfaces\WorkflowActionRepositoryInterface;

class WorkflowActionRepository implements WorkflowActionRepositoryInterface
{
    public function create(array $data): object
    {
        return WorkflowAction::query()->create($data);
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowAction::query()->find($id, $columns);
    }

    public function getByWorkflowDefinition(int|string $workflowDefinitionId): array
    {
        return WorkflowAction::query()->where('workflow_definition_id', $workflowDefinitionId)->get()->all();
    }

    public function getByStep(int|string $stepId): array
    {
        return WorkflowAction::query()->where('step_id', $stepId)->get()->all();
    }
}
