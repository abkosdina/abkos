<?php

namespace App\Services\Workflow;

use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowTransition;
use App\Models\WorkflowInstanceStep;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalDecision;

class ActionContextBuilder
{
    public function buildForWorkflow(
        WorkflowInstance $workflowInstance,
        ?WorkflowTransition $transition = null,
        ?WorkflowInstanceStep $workflowStep = null,
        ?User $actor = null,
        ?array $conditionResult = null,
        array $metadata = []
    ): ActionContext {
        return new ActionContext([
            'workflowInstance' => $workflowInstance,
            'workflowTransition' => $transition,
            'workflowStep' => $workflowStep,
            'actor' => $actor,
            'businessEntityType' => $workflowInstance->entity_type,
            'businessEntityId' => $workflowInstance->entity_id,
            'businessEntity' => $workflowInstance->getEntity(),
            'conditionResult' => $conditionResult,
            'metadata' => $metadata,
        ]);
    }

    public function buildForApproval(
        ApprovalInstance $approvalInstance,
        ?ApprovalInstanceStep $approvalStep = null,
        ?ApprovalDecision $approvalDecision = null,
        ?User $actor = null,
        ?array $conditionResult = null,
        array $metadata = []
    ): ActionContext {
        $workflowInstance = $approvalInstance->workflowInstance;

        return new ActionContext([
            'workflowInstance' => $workflowInstance,
            'approvalInstance' => $approvalInstance,
            'approvalStep' => $approvalStep,
            'approvalDecision' => $approvalDecision,
            'actor' => $actor,
            'businessEntityType' => $workflowInstance->entity_type,
            'businessEntityId' => $workflowInstance->entity_id,
            'businessEntity' => $workflowInstance->getEntity(),
            'conditionResult' => $conditionResult,
            'metadata' => $metadata,
        ]);
    }
}
