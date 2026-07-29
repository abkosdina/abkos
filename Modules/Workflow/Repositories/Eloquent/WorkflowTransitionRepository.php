<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowTransition;
use Modules\Workflow\Repositories\Interfaces\WorkflowTransitionRepositoryInterface;

class WorkflowTransitionRepository implements WorkflowTransitionRepositoryInterface
{
    public function getByFromStep(int|string $fromStepId): array
    {
        return WorkflowTransition::query()->where('from_step_id', $fromStepId)->orderBy('sort_order')->get()->all();
    }

    public function getByWorkflowDefinition(int|string $workflowDefinitionId): array
    {
        return WorkflowTransition::query()->where('workflow_definition_id', $workflowDefinitionId)->orderBy('sort_order')->get()->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowTransition::query()->find($id, $columns);
    }
}
