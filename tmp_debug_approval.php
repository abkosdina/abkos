<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::factory()->create();
$wf = Modules\Workflow\Models\WorkflowDefinition::create([
    'name' => 'dbg',
    'key' => 'dbg'.random_int(1, 9999),
    'entity_type' => 'generic_entity',
    'version' => 1,
    'is_active' => true,
    'is_default' => true,
    'created_by' => $user->id,
    'updated_by' => $user->id,
]);
$ws = Modules\Workflow\Models\WorkflowState::create([
    'workflow_definition_id' => $wf->id,
    'name' => 'Submitted',
    'key' => 'submitted'.random_int(1, 9999),
    'is_initial' => true,
    'is_final' => false,
]);
$wi = Modules\Workflow\Models\WorkflowInstance::create([
    'uuid' => (string) Illuminate\Support\Str::uuid(),
    'workflow_definition_id' => $wf->id,
    'entity_type' => 'generic_entity',
    'entity_id' => 1,
    'current_state_id' => $ws->id,
    'status' => 'active',
    'version' => 1,
]);
$ad = Modules\Workflow\Models\ApprovalDefinition::create([
    'workflow_definition_id' => $wf->id,
    'name' => 'dbg',
    'key' => 'dbg1'.random_int(1, 9999),
    'approval_mode' => 'sequential',
    'required_approval_count' => 1,
    'is_active' => true,
    'created_by' => $user->id,
    'updated_by' => $user->id,
]);
Modules\Workflow\Models\ApprovalStep::create([
    'approval_definition_id' => $ad->id,
    'name' => 'Step 1',
    'key' => 's1'.random_int(1, 9999),
    'sort_order' => 1,
    'required_permission' => 'review',
    'required_approval_count' => 1,
    'can_reject' => true,
    'can_return_for_correction' => true,
    'can_delegate' => false,
]);
Modules\Workflow\Models\ApprovalStep::create([
    'approval_definition_id' => $ad->id,
    'name' => 'Step 2',
    'key' => 's2'.random_int(1, 9999),
    'sort_order' => 2,
    'required_permission' => 'review',
    'required_approval_count' => 1,
    'can_reject' => true,
    'can_return_for_correction' => true,
    'can_delegate' => false,
]);
$engine = app(Modules\Workflow\Interfaces\ApprovalEngineInterface::class);
$instance = $engine->start($wi, $ad);
$step = $instance->approvalInstanceSteps()->where('status', 'active')->first();
$engine->approve($instance, $user, ['approval_instance_step_id' => $step->id, 'idempotency_key' => 'dbg1']);
$instance->refresh();
echo $instance->approvalInstanceSteps()->count(), PHP_EOL;
echo $instance->approvalInstanceSteps()->pluck('status')->implode(','), PHP_EOL;
