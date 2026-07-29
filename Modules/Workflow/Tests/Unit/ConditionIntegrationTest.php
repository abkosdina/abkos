<?php

namespace Modules\Workflow\Tests\Unit;

use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowState;
use App\Models\WorkflowTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workflow\Exceptions\ConditionEvaluationException;
use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Modules\Workflow\Models\ConditionDefinition;
use Modules\Workflow\Models\ConditionGroup;
use Modules\Workflow\Models\ConditionRule;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Enums\ApprovalInstanceStepStatus;
use Modules\Workflow\Enums\ApprovalStatus;
use Modules\Workflow\Enums\ApprovalMode;
use Tests\TestCase;

class ConditionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_transition_with_passing_condition_succeeds(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $definition = WorkflowDefinition::create([
            'name' => 'KYC Workflow',
            'key' => 'kyc-workflow',
            'entity_type' => 'KYC',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
        ]);

        $fromState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Pending',
            'key' => 'pending',
            'is_initial' => true,
            'is_active' => true,
        ]);

        $toState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Approved',
            'key' => 'approved',
            'is_active' => true,
        ]);

        $transition = WorkflowTransition::create([
            'workflow_definition_id' => $definition->id,
            'from_state_id' => $fromState->id,
            'to_state_id' => $toState->id,
            'name' => 'Approve',
            'key' => 'approve',
            'is_active' => true,
            'configuration' => ['pre_conditions' => [['condition_definition_id' => 1]]],
        ]);

        $conditionDefinition = ConditionDefinition::create([
            'name' => 'KYC Gate',
            'key' => 'kyc-gate',
            'version' => 1,
            'is_active' => true,
        ]);

        $group = ConditionGroup::create([
            'condition_definition_id' => $conditionDefinition->id,
            'logical_operator' => 'AND',
            'sort_order' => 1,
        ]);

        ConditionRule::create([
            'condition_definition_id' => $conditionDefinition->id,
            'condition_group_id' => $group->id,
            'field_path' => 'user.kyc_status',
            'provider' => 'context',
            'operator' => 'equals',
            'expected_value' => 'approved',
            'sort_order' => 1,
        ]);

        $transition->update(['configuration' => ['pre_conditions' => [['condition_definition_id' => $conditionDefinition->id]]]]);

        $instance = WorkflowInstance::create([
            'workflow_definition_id' => $definition->id,
            'entity_type' => 'KYC',
            'entity_id' => 100,
            'current_state_id' => $fromState->id,
            'status' => 'active',
            'version' => 1,
            'metadata' => ['user' => ['kyc_status' => 'approved']],
        ]);

        $engine = app(\App\Services\Workflow\WorkflowEngine::class);
        $step = $engine->transition($instance, $transition, ['idempotency_key' => 'workflow-pass']);

        $this->assertNotNull($step);
        $this->assertSame($toState->id, $instance->fresh()->current_state_id);
    }

    public function test_workflow_transition_with_failed_condition_does_not_change_state(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $definition = WorkflowDefinition::create([
            'name' => 'KYC Workflow',
            'key' => 'kyc-workflow-2',
            'entity_type' => 'KYC',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
        ]);

        $fromState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Pending',
            'key' => 'pending-2',
            'is_initial' => true,
            'is_active' => true,
        ]);

        $toState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Approved',
            'key' => 'approved-2',
            'is_active' => true,
        ]);

        $transition = WorkflowTransition::create([
            'workflow_definition_id' => $definition->id,
            'from_state_id' => $fromState->id,
            'to_state_id' => $toState->id,
            'name' => 'Approve',
            'key' => 'approve-2',
            'is_active' => true,
            'configuration' => ['pre_conditions' => []],
        ]);

        $conditionDefinition = ConditionDefinition::create([
            'name' => 'KYC Gate',
            'key' => 'kyc-gate-2',
            'version' => 1,
            'is_active' => true,
        ]);

        $group = ConditionGroup::create([
            'condition_definition_id' => $conditionDefinition->id,
            'logical_operator' => 'AND',
            'sort_order' => 1,
        ]);

        ConditionRule::create([
            'condition_definition_id' => $conditionDefinition->id,
            'condition_group_id' => $group->id,
            'field_path' => 'user.score',
            'provider' => 'context',
            'operator' => 'greater_than_or_equal',
            'expected_value' => 4,
            'sort_order' => 1,
        ]);

        $transition->update(['configuration' => ['pre_conditions' => [['condition_definition_id' => $conditionDefinition->id]]]]);

        $instance = WorkflowInstance::create([
            'workflow_definition_id' => $definition->id,
            'entity_type' => 'KYC',
            'entity_id' => 101,
            'current_state_id' => $fromState->id,
            'status' => 'active',
            'version' => 1,
            'metadata' => ['user' => ['score' => 3.5]],
        ]);

        $engine = app(\App\Services\Workflow\WorkflowEngine::class);

        $this->expectException(ConditionEvaluationException::class);
        $engine->transition($instance, $transition, ['idempotency_key' => 'workflow-fail']);
    }

    public function test_approval_with_failed_condition_does_not_persist_decision(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workflowDefinition = WorkflowDefinition::create([
            'name' => 'Approval Workflow',
            'key' => 'approval-workflow',
            'entity_type' => 'KYC',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
        ]);

        $workflowState = WorkflowState::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Pending',
            'key' => 'pending',
            'is_initial' => true,
            'is_active' => true,
        ]);

        $workflowInstance = WorkflowInstance::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'entity_type' => 'KYC',
            'entity_id' => 200,
            'current_state_id' => $workflowState->id,
            'status' => 'active',
            'version' => 1,
            'metadata' => ['user' => ['kyc_status' => 'pending']],
        ]);

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Review',
            'key' => 'review',
            'approval_mode' => ApprovalMode::any->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'configuration' => ['pre_conditions' => []],
        ]);

        $approvalStep = ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Review Step',
            'key' => 'review-step',
            'required_approval_count' => 1,
            'is_mandatory' => true,
            'can_reject' => true,
            'can_return_for_correction' => false,
            'can_delegate' => false,
            'configuration' => ['pre_conditions' => []],
        ]);

        $approvalInstance = ApprovalInstance::create([
            'workflow_instance_id' => $workflowInstance->id,
            'approval_definition_id' => $approvalDefinition->id,
            'status' => ApprovalStatus::in_progress->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
            'started_at' => now(),
        ]);

        $approvalInstanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $approvalStep->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
            'started_at' => now(),
        ]);

        $conditionDefinition = ConditionDefinition::create([
            'name' => 'Approval Gate',
            'key' => 'approval-gate',
            'version' => 1,
            'is_active' => true,
        ]);

        $group = ConditionGroup::create([
            'condition_definition_id' => $conditionDefinition->id,
            'logical_operator' => 'AND',
            'sort_order' => 1,
        ]);

        ConditionRule::create([
            'condition_definition_id' => $conditionDefinition->id,
            'condition_group_id' => $group->id,
            'field_path' => 'user.kyc_status',
            'provider' => 'context',
            'operator' => 'equals',
            'expected_value' => 'approved',
            'sort_order' => 1,
        ]);

        $approvalDefinition->update(['configuration' => ['pre_conditions' => [['condition_definition_id' => $conditionDefinition->id]]]]);
        $approvalStep->update(['configuration' => ['pre_conditions' => [['condition_definition_id' => $conditionDefinition->id]]]]);

        $engine = app(ApprovalEngineInterface::class);

        $this->expectException(ConditionEvaluationException::class);
        $engine->approve($approvalInstance, $user, ['approval_instance_step_id' => $approvalInstanceStep->id, 'idempotency_key' => 'approval-fail']);

        $this->assertSame(0, ApprovalDecision::query()->count());
    }
}
