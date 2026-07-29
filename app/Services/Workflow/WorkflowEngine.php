<?php

namespace App\Services\Workflow;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowState;
use App\Models\WorkflowTransition;
use App\Repositories\Contracts\WorkflowDefinitionRepository;
use App\Repositories\Contracts\WorkflowInstanceRepository;
use App\Repositories\Contracts\WorkflowStateRepository;
use App\Repositories\Contracts\WorkflowTransitionRepository;
use App\Repositories\Contracts\WorkflowStepRepository;
use App\Repositories\Contracts\WorkflowIdempotencyRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Modules\Workflow\Exceptions\ConditionEvaluationException;
use Modules\Workflow\Models\ConditionDefinition;
use Modules\Workflow\Services\ConditionContextBuilder;
use Modules\Workflow\Services\ConditionEvaluationService;

/**
 * WorkflowEngine
 * 
 * Generic workflow orchestration engine.
 * This is completely independent of any entity type.
 * It only knows about:
 * - Workflow Definitions
 * - Workflow States
 * - Workflow Transitions
 * - Workflow Instances
 * 
 * It does NOT know about Advertisements, KYC, Orders, etc.
 */
class WorkflowEngine
{
    protected WorkflowDefinitionRepository $definitionRepository;
    protected WorkflowInstanceRepository $instanceRepository;
    protected WorkflowStateRepository $stateRepository;
    protected WorkflowTransitionRepository $transitionRepository;
    protected WorkflowStepRepository $stepRepository;
    protected WorkflowIdempotencyRepository $idempotencyRepository;
    protected WorkflowAuthorizationService $authorizationService;
    protected WorkflowLockingService $lockingService;
    protected WorkflowIdempotencyService $idempotencyService;
    protected ConditionEvaluationService $conditionEvaluationService;
    protected ConditionContextBuilder $conditionContextBuilder;

    public function __construct(
        WorkflowDefinitionRepository $definitionRepository,
        WorkflowInstanceRepository $instanceRepository,
        WorkflowStateRepository $stateRepository,
        WorkflowTransitionRepository $transitionRepository,
        WorkflowStepRepository $stepRepository,
        WorkflowIdempotencyRepository $idempotencyRepository,
        WorkflowAuthorizationService $authorizationService,
        WorkflowLockingService $lockingService,
        WorkflowIdempotencyService $idempotencyService,
        ConditionEvaluationService $conditionEvaluationService,
        ConditionContextBuilder $conditionContextBuilder
    ) {
        $this->definitionRepository = $definitionRepository;
        $this->instanceRepository = $instanceRepository;
        $this->stateRepository = $stateRepository;
        $this->transitionRepository = $transitionRepository;
        $this->stepRepository = $stepRepository;
        $this->idempotencyRepository = $idempotencyRepository;
        $this->authorizationService = $authorizationService;
        $this->lockingService = $lockingService;
        $this->idempotencyService = $idempotencyService;
        $this->conditionEvaluationService = $conditionEvaluationService;
        $this->conditionContextBuilder = $conditionContextBuilder;
    }

    /**
     * Start a workflow for an entity
     * 
     * This creates a WorkflowInstance and moves to the initial state
     */
    public function start(
        WorkflowDefinition $definition,
        string $entityType,
        int $entityId,
        array $metadata = []
    ): WorkflowInstance {
        return DB::transaction(function () use ($definition, $entityType, $entityId, $metadata) {
            // Get initial state
            $initialState = $this->stateRepository->getInitialState($definition->id);
            if (!$initialState) {
                throw new \Exception("No initial state defined for workflow: {$definition->key}");
            }

            $instance = $this->instanceRepository->create([
                'workflow_definition_id' => $definition->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'current_state_id' => $initialState->id,
                'status' => 'active',
                'version' => 1,
                'started_at' => now(),
                'metadata' => $metadata,
            ]);

            // Dispatch event
            Event::dispatch(new \App\Events\Workflow\WorkflowStarted($instance));

            return $instance;
        });
    }

    /**
     * Execute a transition
     * 
     * This is the core workflow method that handles state transitions
     */
    public function transition(
        WorkflowInstance $instance,
        WorkflowTransition $transition,
        array $payload = []
    ): WorkflowInstanceStep {
        return DB::transaction(function () use ($instance, $transition, $payload) {
            $instance = $this->lockingService->lock($instance);

            if (!$instance->isActive()) {
                throw new \Exception('Workflow instance is not active');
            }

            $idempotencyKey = $payload['idempotency_key'] ?? null;
            if ($idempotencyKey) {
                $existingStep = $this->stepRepository->findByIdempotencyKey($idempotencyKey);
                if ($existingStep) {
                    return $existingStep;
                }

                if ($this->idempotencyService->isDuplicate($idempotencyKey, $instance->id)) {
                    return $this->stepRepository->findByIdempotencyKey($idempotencyKey) ?? $this->stepRepository->getLastStep($instance->id);
                }
            }

            if ($instance->current_state_id !== $transition->from_state_id) {
                throw new \Exception(
                    'Cannot transition: current state is ' . ($instance->currentState?->key ?? 'unknown')
                    . ' but transition expects ' . ($transition->fromState?->key ?? 'unknown')
                );
            }

            if (!$transition->isActive()) {
                throw new \Exception('Transition is not active');
            }

            $conditionDefinition = $this->resolveConditionDefinition($transition, $payload);
            if ($conditionDefinition) {
                $conditionContext = $this->conditionContextBuilder->buildForWorkflow($instance, $transition, Auth::user(), $payload);
                $conditionResult = $this->conditionEvaluationService->evaluate($conditionDefinition, $conditionContext, [
                    'subject_type' => WorkflowInstance::class,
                    'subject_id' => $instance->id,
                ]);

                if (! $conditionResult['passed']) {
                    throw new ConditionEvaluationException($conditionResult['explanation'] ?? 'Workflow transition conditions were not satisfied.', [
                        'workflow_instance_id' => $instance->id,
                        'workflow_transition_id' => $transition->id,
                        'condition_result' => $conditionResult,
                    ]);
                }
            }

            $this->authorizationService->authorize(Auth::user(), $transition, $instance);

            $previousState = $instance->currentState;
            $newState = $transition->toState;

            $instance->update([
                'current_state_id' => $newState->id,
                'version' => $instance->version + 1,
            ]);

            if ($newState->is_final) {
                $instance->update(['completed_at' => now(), 'status' => 'completed']);
            }

            $step = $this->stepRepository->create([
                'workflow_instance_id' => $instance->id,
                'transition_id' => $transition->id,
                'from_state_id' => $previousState->id,
                'to_state_id' => $newState->id,
                'executed_by' => auth()->id(),
                'idempotency_key' => $idempotencyKey,
                'comment' => $payload['comment'] ?? null,
                'reason' => $payload['reason'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
                'executed_at' => now(),
            ]);

            if ($idempotencyKey) {
                $this->idempotencyService->record($idempotencyKey, $instance, $transition);
            }

            Event::dispatch(new \App\Events\Workflow\WorkflowTransitioned($instance, $previousState, $newState, $step));

            return $step;
        });
    }

    /**
     * Check if a transition is allowed from current state
     */
    public function canTransition(WorkflowInstance $instance, WorkflowTransition $transition): bool
    {
        // Validate current state matches transition's from_state
        if ($instance->current_state_id !== $transition->from_state_id) {
            return false;
        }

        // Validate transition is active
        if (!$transition->isActive()) {
            return false;
        }

        // Validate instance is active
        if (!$instance->isActive()) {
            return false;
        }

        return true;
    }

    /**
     * Get available transitions from current state
     */
    public function getAvailableTransitions(WorkflowInstance $instance): \Illuminate\Database\Eloquent\Collection
    {
        return $this->transitionRepository->findFromState($instance->current_state_id);
    }

    /**
     * Complete a workflow (mark as completed)
     */
    protected function resolveConditionDefinition(WorkflowTransition $transition, array $payload = []): ?ConditionDefinition
    {
        $definitionId = Arr::get($payload, 'condition_definition_id');
        if ($definitionId) {
            return ConditionDefinition::query()->find($definitionId);
        }

        $configuration = $transition->configuration ?? [];
        $conditions = Arr::get($configuration, 'pre_conditions', []);
        if ($conditions === []) {
            return null;
        }

        $firstCondition = $conditions[0] ?? null;
        if (! isset($firstCondition['condition_definition_id'])) {
            return null;
        }

        return ConditionDefinition::query()->find($firstCondition['condition_definition_id']);
    }

    public function complete(WorkflowInstance $instance): WorkflowInstance
    {
        $instance->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Event::dispatch(new \App\Events\Workflow\WorkflowCompleted($instance));

        return $instance;
    }

    /**
     * Cancel a workflow
     */
    public function cancel(WorkflowInstance $instance, string $reason = ''): WorkflowInstance
    {
        $instance->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge($instance->metadata ?? [], ['cancellation_reason' => $reason]),
        ]);

        Event::dispatch(new \App\Events\Workflow\WorkflowCancelled($instance));

        return $instance;
    }

    /**
     * Get current state of instance
     */
    public function getCurrentState(WorkflowInstance $instance): WorkflowState
    {
        return $instance->currentState;
    }

    /**
     * Get workflow instance for an entity
     */
    public function getInstance(
        string $entityType,
        int $entityId,
        ?int $definitionId = null
    ): ?WorkflowInstance {
        return $this->instanceRepository->findForEntity($entityType, $entityId, $definitionId);
    }

    /**
     * Get workflow definition by key
     */
    public function getDefinition(string $key, ?int $version = null): ?WorkflowDefinition
    {
        return $this->definitionRepository->findByKey($key, $version);
    }
}
