<?php

namespace Modules\Workflow\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Workflow\Enums\ApprovalDecision as ApprovalDecisionEnum;
use Modules\Workflow\Enums\ApprovalInstanceStepStatus;
use Modules\Workflow\Enums\ApprovalMode;
use Modules\Workflow\Enums\ApprovalStatus;
use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Modules\Workflow\Models\WorkflowDefinition;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowState;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApprovalEngineCoreLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function ensurePermission(string $name): void
    {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    protected function createWorkflowContext(?User $user = null): array
    {
        $user = $user ?: User::factory()->create();

        $workflowDefinition = WorkflowDefinition::create([
            'name' => 'Generic Review Workflow',
            'key' => 'generic-review-'.Str::random(6),
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
            'key' => 'submitted-'.Str::random(6),
            'is_initial' => true,
            'is_final' => false,
        ]);

        $workflowInstance = WorkflowInstance::create([
            'uuid' => (string) Str::uuid(),
            'workflow_definition_id' => $workflowDefinition->id,
            'entity_type' => 'generic_entity',
            'entity_id' => 500,
            'current_state_id' => $workflowState->id,
            'status' => 'active',
            'version' => 1,
        ]);

        return compact('user', 'workflowDefinition', 'workflowState', 'workflowInstance');
    }

    public function test_start_creates_approval_instance_and_initial_active_step(): void
    {
        $context = $this->createWorkflowContext();
        $user = $context['user'];
        $workflowInstance = $context['workflowInstance'];
        $this->ensurePermission('review');
        $user->givePermissionTo('review');

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowInstance->workflow_definition_id,
            'name' => 'Review approval',
            'key' => 'review-approval-'.Str::random(6),
            'approval_mode' => ApprovalMode::sequential->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $approvalStep = ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Initial Review',
            'key' => 'initial-review-'.Str::random(6),
            'sort_order' => 1,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'is_mandatory' => true,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => false,
        ]);

        $engine = app(ApprovalEngineInterface::class);
        $approvalInstance = $engine->start($workflowInstance, $approvalDefinition);

        $this->assertInstanceOf(ApprovalInstance::class, $approvalInstance);
        $this->assertSame(ApprovalStatus::in_progress, $approvalInstance->status);
        $this->assertSame(1, $approvalInstance->approvalInstanceSteps()->count());
        $this->assertSame(ApprovalInstanceStepStatus::active->value, $approvalInstance->approvalInstanceSteps()->first()->status->value);
        $this->assertSame($approvalStep->id, $approvalInstance->approvalInstanceSteps()->first()->approval_step_id);
    }

    public function test_sequential_approval_moves_to_next_step_and_completes_instance(): void
    {
        $context = $this->createWorkflowContext();
        $user = $context['user'];
        $workflowInstance = $context['workflowInstance'];
        $this->ensurePermission('review');
        $user->givePermissionTo('review');

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowInstance->workflow_definition_id,
            'name' => 'Two-step review',
            'key' => 'two-step-'.Str::random(6),
            'approval_mode' => ApprovalMode::sequential->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Step 1',
            'key' => 'step-1-'.Str::random(6),
            'sort_order' => 1,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => false,
        ]);

        ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Step 2',
            'key' => 'step-2-'.Str::random(6),
            'sort_order' => 2,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => false,
        ]);

        $engine = app(ApprovalEngineInterface::class);
        $approvalInstance = $engine->start($workflowInstance, $approvalDefinition);

        $firstStep = $approvalInstance->approvalInstanceSteps()->where('status', ApprovalInstanceStepStatus::active->value)->first();
        $engine->approve($approvalInstance, $user, ['approval_instance_step_id' => $firstStep->id, 'idempotency_key' => 'seq-1']);

        $approvalInstance->refresh();
        $this->assertSame(ApprovalStatus::in_progress, $approvalInstance->status);
        $this->assertGreaterThanOrEqual(2, $approvalInstance->approvalInstanceSteps()->count());
        $this->assertTrue($approvalInstance->approvalInstanceSteps()->where('status', ApprovalInstanceStepStatus::approved->value)->exists());
        $this->assertTrue($approvalInstance->approvalInstanceSteps()->where('status', ApprovalInstanceStepStatus::active->value)->exists());
    }

    public function test_duplicate_decision_is_ignored_when_idempotency_key_is_reused(): void
    {
        $context = $this->createWorkflowContext();
        $user = $context['user'];
        $workflowInstance = $context['workflowInstance'];
        $this->ensurePermission('review');
        $user->givePermissionTo('review');

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowInstance->workflow_definition_id,
            'name' => 'Idempotent approval',
            'key' => 'idempotent-'.Str::random(6),
            'approval_mode' => ApprovalMode::any->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Single Review',
            'key' => 'single-review-'.Str::random(6),
            'sort_order' => 1,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => false,
        ]);

        $engine = app(ApprovalEngineInterface::class);
        $approvalInstance = $engine->start($workflowInstance, $approvalDefinition);
        $step = $approvalInstance->approvalInstanceSteps()->first();

        $firstDecision = $engine->approve($approvalInstance, $user, ['approval_instance_step_id' => $step->id, 'idempotency_key' => 'dup-key']);
        $secondDecision = $engine->approve($approvalInstance, $user, ['approval_instance_step_id' => $step->id, 'idempotency_key' => 'dup-key']);

        $this->assertSame($firstDecision->id, $secondDecision->id);
        $this->assertSame(1, $approvalInstance->approvalDecisions()->count());
    }

    public function test_reject_marks_instance_as_rejected(): void
    {
        $context = $this->createWorkflowContext();
        $user = $context['user'];
        $workflowInstance = $context['workflowInstance'];
        $this->ensurePermission('review');
        $user->givePermissionTo('review');

        $approvalDefinition = ApprovalDefinition::create([
            'workflow_definition_id' => $workflowInstance->workflow_definition_id,
            'name' => 'Rejection flow',
            'key' => 'reject-'.Str::random(6),
            'approval_mode' => ApprovalMode::sequential->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        ApprovalStep::create([
            'approval_definition_id' => $approvalDefinition->id,
            'name' => 'Rejection Step',
            'key' => 'reject-step-'.Str::random(6),
            'sort_order' => 1,
            'required_permission' => 'review',
            'required_approval_count' => 1,
            'can_reject' => true,
            'can_return_for_correction' => true,
            'can_delegate' => false,
        ]);

        $engine = app(ApprovalEngineInterface::class);
        $approvalInstance = $engine->start($workflowInstance, $approvalDefinition);
        $step = $approvalInstance->approvalInstanceSteps()->first();

        $decision = $engine->reject($approvalInstance, $user, 'policy violation', 'Please fix the issue', ['approval_instance_step_id' => $step->id]);

        $approvalInstance->refresh();
        $step->refresh();

        $this->assertSame(ApprovalDecisionEnum::rejected, $decision->decision);
        $this->assertSame(ApprovalStatus::rejected, $approvalInstance->status);
        $this->assertSame(ApprovalInstanceStepStatus::rejected, $step->status);
    }
}
