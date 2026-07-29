<?php

namespace Modules\Deals\Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Modules\Deals\Models\Deal;
use App\Models\WorkflowTransition;
use App\Models\WorkflowInstance;
use Modules\Deals\Http\Controllers\DealLifecycleController;
use Modules\Deals\Http\Requests\DealActionRequest;
use Modules\Deals\Repositories\Interfaces\DealRepositoryInterface;
use Modules\Deals\Services\DealWorkflowService;
use App\Services\Workflow\WorkflowEngine;

class DealLifecycleControllerTest extends TestCase
{
    public function test_cancel_transition_not_found_returns_422()
    {
        $user = User::factory()->create();
        $this->be($user);

        $deal = new Deal([
            'uuid' => 'test-deal-1',
            'buyer_id' => $user->id,
            'seller_id' => $user->id + 1,
        ]);

        $repo = $this->createMock(DealRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($deal);

        $workflowService = $this->createMock(DealWorkflowService::class);
        $instance = new WorkflowInstance();
        $instance->id = 123;
        $instance->workflow_definition_id = 999;
        $workflowService->method('createDealWorkflow')->willReturn($instance);

        $engine = $this->createMock(WorkflowEngine::class);

        $controller = new DealLifecycleController($repo, $workflowService, $engine);

        $request = DealActionRequest::create('/','POST', ['reason' => 'no transition']);
        $request->setContainer(app());

        $response = $controller->cancel('test-deal-1', $request);

        $this->assertEquals(422, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertStringContainsString('Transition not found', $content['message']);
    }

    public function test_cancel_transition_success_dispatches_event()
    {
        $user = User::factory()->create();
        $this->be($user);

        $deal = new Deal([
            'uuid' => 'test-deal-2',
            'buyer_id' => $user->id,
            'seller_id' => $user->id,
        ]);

        $repo = $this->createMock(DealRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($deal);

        $workflowService = $this->createMock(DealWorkflowService::class);
        $instance = new WorkflowInstance();
        $instance->id = 555;
        $instance->workflow_definition_id = 1001;
        $workflowService->method('createDealWorkflow')->willReturn($instance);

        // ensure workflow definition and states exist, then insert transition via DB
        $definitionId = \Illuminate\Support\Facades\DB::table('workflow_definitions')->insertGetId([
            'name' => 'Test Definition',
            'key' => 'deal.lifecycle',
            'description' => 'test',
            'entity_type' => 'Deal',
            'version' => 1,
            'is_active' => 1,
            'is_default' => 0,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fromStateId = \Illuminate\Support\Facades\DB::table('workflow_states')->insertGetId([
            'workflow_definition_id' => $definitionId,
            'name' => 'Pending',
            'key' => 'pending',
            'description' => 'pending',
            'is_initial' => 1,
            'is_final' => 0,
            'is_rejection' => 0,
            'is_active' => 1,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $toStateId = \Illuminate\Support\Facades\DB::table('workflow_states')->insertGetId([
            'workflow_definition_id' => $definitionId,
            'name' => 'Cancelled',
            'key' => 'cancelled',
            'description' => 'cancelled',
            'is_initial' => 0,
            'is_final' => 1,
            'is_rejection' => 1,
            'is_active' => 1,
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // ensure the mocked instance reports the created definition id
        $instance->workflow_definition_id = $definitionId;

        \Illuminate\Support\Facades\DB::table('workflow_transitions')->insert([
            'workflow_definition_id' => $definitionId,
            'from_state_id' => $fromStateId,
            'to_state_id' => $toStateId,
            'key' => 'cancel',
            'name' => 'Cancel',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $step = new \App\Models\WorkflowInstanceStep();
        $toState = new \App\Models\WorkflowState(['key' => 'cancelled']);
        $step->setRelation('toState', $toState);

        $engine = $this->createMock(WorkflowEngine::class);
        $engine->method('transition')->willReturn($step);

        $controller = new DealLifecycleController($repo, $workflowService, $engine);

        $request = DealActionRequest::create('/','POST', ['reason' => 'cancel now']);
        $request->setContainer(app());

        $response = $controller->cancel('test-deal-2', $request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('cancelled', $content['data']['new_state']);
    }
}
