<?php

namespace App\Services\Workflow;

use App\Models\WorkflowAction;
use App\Models\WorkflowInstance;
use App\Models\WorkflowTransition;
use App\Models\WorkflowInstanceStep;

class ActionTriggerResolver
{
    public function resolve(
        string $eventName,
        WorkflowInstance $workflowInstance,
        ?WorkflowTransition $transition = null,
        ?WorkflowInstanceStep $step = null
    ): array {
        $query = WorkflowAction::query()
            ->where('event_name', $eventName)
            ->where('is_active', true)
            ->where('workflow_definition_id', $workflowInstance->workflow_definition_id);

        if ($transition) {
            $query->where(function ($builder) use ($transition) {
                $builder->whereNull('workflow_transition_id')
                    ->orWhere('workflow_transition_id', $transition->id);
            });
        }

        if ($step) {
            $query->where(function ($builder) use ($step) {
                $builder->whereNull('step_id')
                    ->orWhere('step_id', $step->id);
            });
        }

        $actions = $query->orderBy('priority')->orderBy('execution_order')->get();

        return $actions->map(fn (WorkflowAction $action) => ActionDefinition::fromModel($action))->all();
    }
}
