<?php

namespace App\Services\Workflow;

use App\Models\User;
use App\Models\WorkflowActionExecution;
use App\Models\WorkflowInstance as AppWorkflowInstance;
use App\Models\WorkflowTransition;
use App\Models\WorkflowInstanceStep;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalDecision;

class ActionContext
{
    public ?AppWorkflowInstance $workflowInstance;
    public ?WorkflowTransition $workflowTransition;
    public ?WorkflowInstanceStep $workflowStep;
    public ?ApprovalInstance $approvalInstance;
    public ?ApprovalInstanceStep $approvalStep;
    public ?ApprovalDecision $approvalDecision;
    public ?User $actor;
    public ?string $businessEntityType;
    public ?int $businessEntityId;
    public ?object $businessEntity;
    public ?array $conditionResult;
    public array $metadata;

    public function __construct(array $attributes = [])
    {
        $this->workflowInstance = $attributes['workflowInstance'] ?? null;
        $this->workflowTransition = $attributes['workflowTransition'] ?? null;
        $this->workflowStep = $attributes['workflowStep'] ?? null;
        $this->approvalInstance = $attributes['approvalInstance'] ?? null;
        $this->approvalStep = $attributes['approvalStep'] ?? null;
        $this->approvalDecision = $attributes['approvalDecision'] ?? null;
        $this->actor = $attributes['actor'] ?? null;
        $this->businessEntityType = $attributes['businessEntityType'] ?? null;
        $this->businessEntityId = $attributes['businessEntityId'] ?? null;
        $this->businessEntity = $attributes['businessEntity'] ?? null;
        $this->conditionResult = $attributes['conditionResult'] ?? null;
        $this->metadata = $attributes['metadata'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'workflow_instance_id' => $this->workflowInstance?->id,
            'workflow_transition_id' => $this->workflowTransition?->id,
            'workflow_step_id' => $this->workflowStep?->id,
            'approval_instance_id' => $this->approvalInstance?->id,
            'approval_step_id' => $this->approvalStep?->id,
            'approval_decision_id' => $this->approvalDecision?->id,
            'actor_id' => $this->actor?->id,
            'business_entity_type' => $this->businessEntityType,
            'business_entity_id' => $this->businessEntityId,
            'condition_result' => $this->conditionResult,
            'metadata' => $this->metadata,
        ];
    }
}
