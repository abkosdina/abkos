<?php

namespace Modules\Workflow\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Workflow\Enums\ApprovalInstanceStepStatus;
use Modules\Workflow\Enums\ApprovalStatus;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Models\ApprovalDelegation;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Modules\Workflow\Models\WorkflowDefinition;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowState;
use Modules\Workflow\Services\ApprovalAuthorizationService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalAuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function createWorkflowContext(): array
    {
        $user = User::factory()->create();

        $workflowDefinition = WorkflowDefinition::create([
            'name' => 'Approval Auth Workflow',
            'key' => 'approval-auth-' . Str::random(6),
            'entity_type' => 'generic_entity',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $workflowState = WorkflowState::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Submitted',
            'key' => 'submitted-' . Str::random(6),
            'is_initial' => true,
            'is_final' => false,
        ]);

        $workflowInstance = WorkflowInstance::create([
            'uuid' => (string) Str::uuid(),
            'workflow_definition_id' => $workflowDefinition->id,
            'entity_type' => 'generic_entity',
            'entity_id' => 777,
            'current_state_id' => $workflowState->id,
            'status' => 'active',
            'version' => 1,
            'metadata' => null,
        ]);

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Auth Test Approval',
            'key' => 'auth-test-' . Str::random(6),
            'approval_mode' => 'sequential',
            'required_approval_count' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalInstance = ApprovalInstance::create([
            'workflow_instance_id' => $workflowInstance->id,
            'approval_definition_id' => $approvalDefinition->id,
            'status' => ApprovalStatus::in_progress->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
            'metadata' => null,
        ]);

        return compact('user', 'workflowInstance', 'approvalDefinition', 'approvalInstance');
    }

    public function test_role_based_approval_authorizes_users_with_required_role(): void
    {
        $service = app(ApprovalAuthorizationService::class);
        $context = $this->createWorkflowContext();
        $creator = $context['user'];
        $approvalInstance = $context['approvalInstance'];

        Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $approver = User::factory()->create();
        $approver->assignRole('reviewer');

        $step = ApprovalStep::create([
            'approval_definition_id' => $context['approvalDefinition']->id,
            'name' => 'Role Review',
            'key' => 'role-review-' . Str::random(6),
            'sort_order' => 1,
            'required_role' => 'reviewer',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => true,
        ]);

        $instanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $step->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
        ]);

        $result = $service->canApprove($approver, $approvalInstance, $instanceStep);

        $this->assertTrue($result->authorized);
        $this->assertSame('authorized', $result->code);

        $unauthorized = User::factory()->create();
        $result2 = $service->canApprove($unauthorized, $approvalInstance, $instanceStep);
        $this->assertFalse($result2->authorized);
    }

    public function test_permission_based_approval_authorizes_users_with_required_permission(): void
    {
        $service = app(ApprovalAuthorizationService::class);
        $context = $this->createWorkflowContext();
        $approvalInstance = $context['approvalInstance'];

        Permission::firstOrCreate(['name' => 'approve.finance', 'guard_name' => 'web']);
        $approver = User::factory()->create();
        $approver->givePermissionTo('approve.finance');

        $step = ApprovalStep::create([
            'approval_definition_id' => $context['approvalDefinition']->id,
            'name' => 'Permission Review',
            'key' => 'permission-review-' . Str::random(6),
            'sort_order' => 2,
            'required_permission' => 'approve.finance',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => true,
        ]);

        $instanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $step->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
        ]);

        $result = $service->canApprove($approver, $approvalInstance, $instanceStep);

        $this->assertTrue($result->authorized);
    }

    public function test_specific_user_and_self_approval_are_enforced(): void
    {
        $service = app(ApprovalAuthorizationService::class);
        $context = $this->createWorkflowContext();
        $creator = $context['user'];
        $approvalInstance = $context['approvalInstance'];

        $approver = User::factory()->create();
        $step = ApprovalStep::create([
            'approval_definition_id' => $context['approvalDefinition']->id,
            'name' => 'User Review',
            'key' => 'user-review-' . Str::random(6),
            'sort_order' => 3,
            'required_user_id' => $approver->id,
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => true,
        ]);

        $instanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $step->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
        ]);

        $this->assertTrue($service->canApprove($approver, $approvalInstance, $instanceStep)->authorized);
        $this->assertFalse($service->canApprove($creator, $approvalInstance, $instanceStep)->authorized);
    }

    public function test_duplicate_decision_and_suspended_user_are_blocked(): void
    {
        $service = app(ApprovalAuthorizationService::class);
        $context = $this->createWorkflowContext();
        $approvalInstance = $context['approvalInstance'];

        Permission::firstOrCreate(['name' => 'approve.finance', 'guard_name' => 'web']);
        $approver = User::factory()->create();
        $approver->givePermissionTo('approve.finance');
        $approveStep = ApprovalStep::create([
            'approval_definition_id' => $context['approvalDefinition']->id,
            'name' => 'Duplicate Check',
            'key' => 'duplicate-check-' . Str::random(6),
            'sort_order' => 4,
            'required_permission' => 'approve.finance',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => true,
        ]);

        $instanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $approveStep->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
        ]);

        ApprovalDecision::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_instance_step_id' => $instanceStep->id,
            'approver_id' => $approver->id,
            'approver_role' => 'reviewer',
            'decision' => 'approved',
            'idempotency_key' => 'dup-' . Str::random(6),
            'decided_at' => now(),
        ]);

        $approver->setAttribute('is_suspended', true);

        $result = $service->canApprove($approver, $approvalInstance, $instanceStep);
        $this->assertFalse($result->authorized);
    }

    public function test_delegate_requires_authorized_and_active_target(): void
    {
        $service = app(ApprovalAuthorizationService::class);
        $context = $this->createWorkflowContext();
        $approvalInstance = $context['approvalInstance'];

        Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $delegator = User::factory()->create();
        $delegator->assignRole('reviewer');
        $delegateTarget = User::factory()->create();
        $delegateTarget->assignRole('reviewer');

        $step = ApprovalStep::create([
            'approval_definition_id' => $context['approvalDefinition']->id,
            'name' => 'Delegation Review',
            'key' => 'delegation-review-' . Str::random(6),
            'sort_order' => 5,
            'required_role' => 'reviewer',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => true,
        ]);

        $instanceStep = ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $step->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => 1,
            'received_approval_count' => 0,
            'version' => 1,
        ]);

        $result = $service->canDelegate($delegator, $approvalInstance, $instanceStep, $delegateTarget);
        $this->assertTrue($result->authorized);

        $invalidTarget = User::factory()->create();
        $invalidTargetResult = $service->canDelegate($delegator, $approvalInstance, $instanceStep, $invalidTarget);
        $this->assertFalse($invalidTargetResult->authorized);
    }
}
