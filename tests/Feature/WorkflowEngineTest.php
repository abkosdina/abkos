<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowIdempotencyKey;
use App\Models\WorkflowState;
use App\Models\WorkflowTransition;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->createWorkflowSchema();
    }

    public function test_it_can_start_and_transition_a_workflow_instance(): void
    {
        $user = User::create([
            'name' => 'Workflow Tester',
            'email' => 'workflow@example.com',
            'password' => bcrypt('password'),
        ]);

        auth()->setUser($user);

        $definition = WorkflowDefinition::create([
            'name' => 'Generic Review',
            'key' => 'generic.review',
            'entity_type' => 'Advertisement',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $initialState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Draft',
            'key' => 'draft',
            'is_initial' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $reviewState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Pending Review',
            'key' => 'pending_review',
            'is_initial' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $transition = WorkflowTransition::create([
            'workflow_definition_id' => $definition->id,
            'from_state_id' => $initialState->id,
            'to_state_id' => $reviewState->id,
            'name' => 'Submit',
            'key' => 'submit',
            'is_active' => true,
        ]);

        $engine = app(WorkflowEngine::class);

        $instance = $engine->start($definition, 'Advertisement', 42, ['source' => 'test']);

        $this->assertInstanceOf(WorkflowInstance::class, $instance);
        $this->assertSame($initialState->id, $instance->current_state_id);
        $this->assertSame('active', $instance->status);

        $step = $engine->transition($instance, $transition, [
            'comment' => 'Submitted for review',
            'idempotency_key' => 'transition-1',
        ]);

        $instance->refresh();

        $this->assertInstanceOf(WorkflowInstanceStep::class, $step);
        $this->assertSame($reviewState->id, $instance->current_state_id);
        $this->assertSame(2, $instance->version);
        $this->assertCount(1, $instance->steps);
    }

    public function test_duplicate_transition_requests_are_idempotent(): void
    {
        $user = User::create([
            'name' => 'Workflow Tester',
            'email' => 'workflow-duplicate@example.com',
            'password' => bcrypt('password'),
        ]);

        auth()->setUser($user);

        $definition = WorkflowDefinition::create([
            'name' => 'Generic Review',
            'key' => 'generic.review.duplicate',
            'entity_type' => 'Advertisement',
            'version' => 1,
            'is_active' => true,
            'is_default' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $initialState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Draft',
            'key' => 'draft',
            'is_initial' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $reviewState = WorkflowState::create([
            'workflow_definition_id' => $definition->id,
            'name' => 'Pending Review',
            'key' => 'pending_review',
            'is_initial' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $transition = WorkflowTransition::create([
            'workflow_definition_id' => $definition->id,
            'from_state_id' => $initialState->id,
            'to_state_id' => $reviewState->id,
            'name' => 'Submit',
            'key' => 'submit',
            'is_active' => true,
        ]);

        $engine = app(WorkflowEngine::class);
        $instance = $engine->start($definition, 'Advertisement', 43);

        $firstStep = $engine->transition($instance, $transition, [
            'comment' => 'First attempt',
            'idempotency_key' => 'duplicate-key',
        ]);

        $secondStep = $engine->transition($instance, $transition, [
            'comment' => 'Second attempt',
            'idempotency_key' => 'duplicate-key',
        ]);

        $this->assertSame($firstStep->id, $secondStep->id);
        $this->assertSame(1, WorkflowIdempotencyKey::where('key', 'duplicate-key')->count());
    }

    protected function createWorkflowSchema(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('workflow_definitions')) {
            Schema::create('workflow_definitions', function ($table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->string('key');
                $table->text('description')->nullable();
                $table->string('entity_type');
                $table->integer('version')->default(1);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('configuration')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['key', 'version']);
            });
        }

        if (!Schema::hasTable('workflow_states')) {
            Schema::create('workflow_states', function ($table) {
                $table->id();
                $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
                $table->string('name');
                $table->string('key');
                $table->text('description')->nullable();
                $table->boolean('is_initial')->default(false);
                $table->boolean('is_final')->default(false);
                $table->boolean('is_rejection')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['workflow_definition_id', 'key']);
            });
        }

        if (!Schema::hasTable('workflow_transitions')) {
            Schema::create('workflow_transitions', function ($table) {
                $table->id();
                $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
                $table->foreignId('from_state_id')->constrained('workflow_states')->cascadeOnDelete();
                $table->foreignId('to_state_id')->constrained('workflow_states')->cascadeOnDelete();
                $table->string('name')->nullable();
                $table->string('key');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('required_role')->nullable();
                $table->string('required_permission')->nullable();
                $table->json('configuration')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['workflow_definition_id', 'key']);
            });
        }

        if (!Schema::hasTable('workflow_instances')) {
            Schema::create('workflow_instances', function ($table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id');
                $table->foreignId('current_state_id')->constrained('workflow_states')->restrictOnDelete();
                $table->string('status')->default('active');
                $table->integer('version')->default(1);
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['entity_type', 'entity_id', 'workflow_definition_id']);
            });
        }

        if (!Schema::hasTable('workflow_instance_steps')) {
            Schema::create('workflow_instance_steps', function ($table) {
                $table->id();
                $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
                $table->foreignId('transition_id')->constrained('workflow_transitions')->restrictOnDelete();
                $table->foreignId('from_state_id')->constrained('workflow_states')->restrictOnDelete();
                $table->foreignId('to_state_id')->constrained('workflow_states')->restrictOnDelete();
                $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('idempotency_key')->nullable()->unique();
                $table->text('comment')->nullable();
                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('executed_at');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('workflow_idempotency_keys')) {
            Schema::create('workflow_idempotency_keys', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
                $table->foreignId('transition_id')->nullable()->constrained('workflow_transitions')->nullOnDelete();
                $table->string('request_hash')->nullable();
                $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('executed_at');
                $table->timestamps();
            });
        }
    }
}
