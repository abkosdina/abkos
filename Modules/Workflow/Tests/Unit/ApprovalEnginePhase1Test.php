<?php

namespace Modules\Workflow\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Workflow\Enums\ApprovalDecision as ApprovalDecisionEnum;
use Modules\Workflow\Enums\ApprovalDelegationStatus;
use Modules\Workflow\Enums\ApprovalInstanceStepStatus;
use Modules\Workflow\Enums\ApprovalMode;
use Modules\Workflow\Enums\ApprovalStatus;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Models\ApprovalDelegation;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Modules\Workflow\Models\WorkflowDefinition;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowInstanceStep;
use Modules\Workflow\Models\WorkflowState;
use Modules\Workflow\Models\WorkflowTransition;
use Tests\TestCase;

class ApprovalEnginePhase1Test extends TestCase
{
    use RefreshDatabase;

    public function test_approval_definition_creation_and_relationships(): void
    {
        $user = User::factory()->create();
        $workflowDefinition = WorkflowDefinition::create([
            'name' => 'Generic Review Workflow',
            'key' => 'generic-review',
            'entity_type' => 'generic_entity',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Generic Approval Definition',
            'key' => 'generic-approval',
            'description' => 'A generic approval definition',
            'approval_mode' => ApprovalMode::any->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'configuration' => ['threshold' => 1],
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertNotNull($approvalDefinition->uuid);
        $this->assertTrue(Str::isUuid($approvalDefinition->uuid));
        $this->assertSame($workflowDefinition->id, $approvalDefinition->workflow_definition_id);
        $this->assertTrue($approvalDefinition->workflowDefinition->is($workflowDefinition));
        $this->assertTrue($approvalDefinition->creator->is($user));
        $this->assertInstanceOf(ApprovalMode::class, $approvalDefinition->approval_mode);
        $this->assertSame(ApprovalMode::any, $approvalDefinition->approval_mode);
    }

    public function test_approval_steps_instances_and_steps_can_be_created(): void
    {
        $user = User::factory()->create();
        $workflowDefinition = WorkflowDefinition::create([
            'name' => 'Generic Review Workflow',
            'key' => 'generic-review-2',
            'entity_type' => 'generic_entity',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Generic Approval Definition',
            'key' => 'generic-approval-2',
            'approval_mode' => ApprovalMode::parallel->value,
            'required_approval_count' => 2,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalStep = ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Initial Review',
            'key' => 'initial-review',
            'description' => 'First approval gate',
            'sort_order' => 1,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'is_mandatory' => true,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => true,
            'configuration' => ['allow_any' => true],
            'metadata' => ['source' => 'tests'],
        ]);

        $workflowState = WorkflowState::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Submitted',
            'key' => 'submitted',
            'is_initial' => true,
            'is_final' => false,
        ]);

        $workflowInstance = WorkflowInstance::create([
            'uuid' => (string) Str::uuid(),
            'workflow_definition_id' => $workflowDefinition->id,
            'entity_type' => 'generic_entity',
            'entity_id' => 99,
            'current_state_id' => $workflowState->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $approvalInstance = ApprovalInstance::create([
            'workflow_instance_id' => $workflowInstance->id,
            'approval_definition_id' => $approvalDefinition->id,
            'status' => ApprovalStatus::pending->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
            'metadata' => ['source' => 'tests'],
        ]);

        $approvalInstanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $approvalStep->id,
            'status' => ApprovalInstanceStepStatus::pending->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
            'metadata' => ['source' => 'tests'],
        ]);

        $this->assertTrue($approvalInstance->workflowInstance->is($workflowInstance));
        $this->assertTrue($approvalInstance->approvalDefinition->is($approvalDefinition));
        $this->assertTrue($approvalInstanceStep->approvalInstance->is($approvalInstance));
        $this->assertTrue($approvalInstanceStep->approvalStep->is($approvalStep));
        $this->assertInstanceOf(ApprovalStatus::class, $approvalInstance->status);
        $this->assertInstanceOf(ApprovalInstanceStepStatus::class, $approvalInstanceStep->status);
    }

    public function test_approval_decisions_support_idempotency_and_relationships(): void
    {
        $user = User::factory()->create();
        $workflowDefinition = WorkflowDefinition::create([
            'name' => 'Generic Review Workflow',
            'key' => 'generic-review-3',
            'entity_type' => 'generic_entity',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Generic Approval Definition',
            'key' => 'generic-approval-3',
            'approval_mode' => ApprovalMode::all->value,
            'required_approval_count' => 2,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalStep = ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Final Review',
            'key' => 'final-review',
            'sort_order' => 2,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'is_mandatory' => true,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => false,
        ]);

        $workflowState = WorkflowState::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Submitted',
            'key' => 'submitted-3',
            'is_initial' => true,
            'is_final' => false,
        ]);

        $workflowInstance = WorkflowInstance::create([
            'uuid' => (string) Str::uuid(),
            'workflow_definition_id' => $workflowDefinition->id,
            'entity_type' => 'generic_entity',
            'entity_id' => 100,
            'current_state_id' => $workflowState->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $approvalInstance = ApprovalInstance::create([
            'workflow_instance_id' => $workflowInstance->id,
            'approval_definition_id' => $approvalDefinition->id,
            'status' => ApprovalStatus::in_progress->value,
            'required_approval_count' => 2,
            'received_approval_count' => 1,
            'version' => 1,
        ]);

        $approvalInstanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $approvalStep->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => 1,
            'received_approval_count' => 1,
            'version' => 1,
        ]);

        $decision = ApprovalDecision::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_instance_step_id' => $approvalInstanceStep->id,
            'approver_id' => $user->id,
            'approver_role' => 'reviewer',
            'decision' => ApprovalDecisionEnum::approved->value,
            'comment' => 'Looks good',
            'reason' => 'meets criteria',
            'idempotency_key' => 'decision-1',
            'metadata' => ['source' => 'tests'],
            'decided_at' => now(),
        ]);

        $this->assertNotNull($decision->uuid);
        $this->assertTrue(Str::isUuid($decision->uuid));
        $this->assertTrue($decision->approvalInstance->is($approvalInstance));
        $this->assertTrue($decision->approvalInstanceStep->is($approvalInstanceStep));
        $this->assertTrue($decision->approver->is($user));
        $this->assertInstanceOf(ApprovalDecisionEnum::class, $decision->decision);
        $this->assertSame(ApprovalDecisionEnum::approved, $decision->decision);

        $this->expectException(\Illuminate\Database\QueryException::class);
        ApprovalDecision::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_instance_step_id' => $approvalInstanceStep->id,
            'approver_id' => $user->id,
            'approver_role' => 'reviewer',
            'decision' => ApprovalDecisionEnum::rejected->value,
            'comment' => 'Duplicate',
            'reason' => 'duplicate',
            'idempotency_key' => 'decision-1',
            'decided_at' => now(),
        ]);
    }

    public function test_approval_delegations_can_be_created_with_status_cast(): void
    {
        $user = User::factory()->create();
        $delegator = User::factory()->create();
        $delegatee = User::factory()->create();
        $workflowDefinition = WorkflowDefinition::create([
            'name' => 'Generic Review Workflow',
            'key' => 'generic-review-4',
            'entity_type' => 'generic_entity',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Generic Approval Definition',
            'key' => 'generic-approval-4',
            'approval_mode' => ApprovalMode::sequential->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalStep = ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Delegated Review',
            'key' => 'delegated-review',
            'sort_order' => 3,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'is_mandatory' => true,
            'can_reject' => true,
            'can_return_for_correction' => false,
            'can_delegate' => true,
        ]);

        $workflowState = WorkflowState::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Submitted',
            'key' => 'submitted-4',
            'is_initial' => true,
            'is_final' => false,
        ]);

        $workflowInstance = WorkflowInstance::create([
            'uuid' => (string) Str::uuid(),
            'workflow_definition_id' => $workflowDefinition->id,
            'entity_type' => 'generic_entity',
            'entity_id' => 101,
            'current_state_id' => $workflowState->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $approvalInstance = ApprovalInstance::create([
            'workflow_instance_id' => $workflowInstance->id,
            'approval_definition_id' => $approvalDefinition->id,
            'status' => ApprovalStatus::pending->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
        ]);

        $approvalInstanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $approvalStep->id,
            'status' => ApprovalInstanceStepStatus::pending->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
        ]);

        $delegation = ApprovalDelegation::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_instance_step_id' => $approvalInstanceStep->id,
            'delegated_from' => $delegator->id,
            'delegated_to' => $delegatee->id,
            'reason' => 'busy',
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'status' => ApprovalDelegationStatus::pending->value,
            'created_by' => $user->id,
        ]);

        $this->assertTrue($delegation->approvalInstance->is($approvalInstance));
        $this->assertTrue($delegation->approvalInstanceStep->is($approvalInstanceStep));
        $this->assertTrue($delegation->delegatedFromUser->is($delegator));
        $this->assertTrue($delegation->delegatedToUser->is($delegatee));
        $this->assertTrue($delegation->creator->is($user));
        $this->assertInstanceOf(ApprovalDelegationStatus::class, $delegation->status);
        $this->assertSame(ApprovalDelegationStatus::pending, $delegation->status);
    }
}
