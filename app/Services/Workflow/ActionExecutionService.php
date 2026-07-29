<?php

namespace App\Services\Workflow;

use App\Models\WorkflowAction;
use App\Models\WorkflowActionExecution;
use App\Models\WorkflowTransition;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;

class ActionExecutionService
{
    public function __construct(
        protected DatabaseManager $db,
        protected ActionHandlerRegistry $registry,
        protected ActionContextBuilder $contextBuilder
    ) {
    }

    public function execute(ActionDefinition $definition, ActionContext $context, string $idempotencyKey = null): ActionResult
    {
        if (! $definition->isActive()) {
            throw new InvalidArgumentException('Action definition is not active.');
        }

        $handlerKey = $definition->getHandlerKey();
        if (! $this->registry->isRegistered($handlerKey)) {
            throw new InvalidArgumentException("Unknown action handler [{$handlerKey}].");
        }

        $handler = $this->registry->resolve($handlerKey);
        $idempotencyKey = $idempotencyKey ?: $this->generateIdempotencyKey($definition, $context);

        $existingExecution = WorkflowActionExecution::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingExecution) {
            if ($existingExecution->status === 'completed') {
                return new ActionResult(true, $existingExecution->result ?? [], 'Action already executed', null, false, $existingExecution->metadata ?? []);
            }

            if ($existingExecution->status === 'running') {
                return new ActionResult(false, null, 'Action already in progress', 'ACTION_IN_PROGRESS', true, []);
            }
        }

        return $this->db->transaction(function () use ($definition, $context, $handler, $idempotencyKey) {
            $execution = WorkflowActionExecution::create([
                'workflow_action_id' => $definition->getId(),
                'workflow_definition_id' => $context->workflowInstance?->workflow_definition_id,
                'workflow_transition_id' => $context->workflowTransition?->id,
                'workflow_instance_id' => $context->workflowInstance?->id,
                'approval_instance_id' => $context->approvalInstance?->id,
                'actor_id' => $context->actor?->id,
                'business_entity_type' => $context->businessEntityType,
                'business_entity_id' => $context->businessEntityId,
                'action_key' => $definition->getKey(),
                'action_version' => $definition->getVersion(),
                'handler' => $definition->getHandlerKey(),
                'status' => 'running',
                'idempotency_key' => $idempotencyKey,
                'max_attempts' => $definition->getMaxAttempts(),
                'backoff_seconds' => $definition->getBackoffSeconds(),
                'metadata' => $definition->getMetadata(),
                'started_at' => now(),
                'result' => null,
            ]);

            try {
                $result = $handler->handle($context, array_merge($definition->getConfiguration(), $definition->getPayload()));
            } catch (\Throwable $exception) {
                $execution->status = 'failed';
                $execution->retry_count = 0;
                $execution->attempts = 1;
                $execution->failed_at = now();
                $execution->error_code = 'ACTION_EXCEPTION';
                $execution->error_message = $exception->getMessage();
                $execution->result = ['exception' => $exception->getMessage()];
                $execution->save();

                return ActionResult::failure('ACTION_EXCEPTION', $exception->getMessage(), true, ['exception' => $exception->getMessage()]);
            }

            $execution->status = $result->success ? 'completed' : 'failed';
            $execution->completed_at = now();
            $execution->failed_at = $result->success ? null : now();
            $execution->attempts = $execution->attempts + 1;
            $execution->retry_count = $result->success ? $execution->retry_count : $execution->retry_count + 1;
            $execution->result = $result->toArray();
            $execution->error_code = $result->errorCode;
            $execution->error_message = $result->success ? null : $result->message;
            $execution->next_retry_at = $result->retryable ? now()->addSeconds($execution->backoff_seconds) : null;
            $execution->save();

            return $result;
        });
    }

    public function createPendingExecution(ActionDefinition $definition, ActionContext $context, string $idempotencyKey = null): WorkflowActionExecution
    {
        $idempotencyKey = $idempotencyKey ?: $this->generateIdempotencyKey($definition, $context);

        return WorkflowActionExecution::firstOrCreate([
            'idempotency_key' => $idempotencyKey,
        ], [
            'workflow_action_id' => $definition->getId(),
            'workflow_definition_id' => $context->workflowInstance?->workflow_definition_id,
            'workflow_transition_id' => $context->workflowTransition?->id,
            'workflow_instance_id' => $context->workflowInstance?->id,
            'approval_instance_id' => $context->approvalInstance?->id,
            'actor_id' => $context->actor?->id,
            'business_entity_type' => $context->businessEntityType,
            'business_entity_id' => $context->businessEntityId,
            'action_key' => $definition->getKey(),
            'action_version' => $definition->getVersion(),
            'handler' => $definition->getHandlerKey(),
            'status' => 'pending',
            'max_attempts' => $definition->getMaxAttempts(),
            'backoff_seconds' => $definition->getBackoffSeconds(),
            'metadata' => $definition->getMetadata(),
        ]);
    }

    public function executePendingExecution(WorkflowActionExecution $execution): ActionResult
    {
        if (! in_array($execution->status, ['pending', 'retrying'], true)) {
            return ActionResult::failure('INVALID_EXECUTION_STATUS', 'Execution is not pending or retrying.', false);
        }

        $action = WorkflowAction::query()->findOrFail($execution->workflow_action_id);
        $definition = ActionDefinition::fromModel($action);
        $context = $this->buildContextFromExecution($execution);

        $execution->status = 'running';
        $execution->started_at = now();
        $execution->save();

        $handler = $this->registry->resolve($definition->getHandlerKey());

        try {
            $result = $handler->handle($context, array_merge($definition->getConfiguration(), $definition->getPayload()));
        } catch (\Throwable $exception) {
            $execution->status = 'failed';
            $execution->attempts += 1;
            $execution->retry_count += 1;
            $execution->failed_at = now();
            $execution->error_code = 'ACTION_EXCEPTION';
            $execution->error_message = $exception->getMessage();
            $execution->result = ['exception' => $exception->getMessage()];
            $execution->next_retry_at = now()->addSeconds($execution->backoff_seconds);
            $execution->save();

            return ActionResult::failure('ACTION_EXCEPTION', $exception->getMessage(), true, ['exception' => $exception->getMessage()]);
        }

        $execution->status = $result->success ? 'completed' : 'failed';
        $execution->completed_at = now();
        $execution->failed_at = $result->success ? null : now();
        $execution->attempts += 1;
        $execution->retry_count = $result->success ? $execution->retry_count : $execution->retry_count + 1;
        $execution->result = $result->toArray();
        $execution->error_code = $result->errorCode;
        $execution->error_message = $result->success ? null : $result->message;
        $execution->next_retry_at = $result->retryable ? now()->addSeconds($execution->backoff_seconds) : null;
        $execution->save();

        return $result;
    }

    public function buildContextFromExecution(WorkflowActionExecution $execution): ActionContext
    {
        $workflowInstance = $execution->workflowInstance;

        return new ActionContext([
            'workflowInstance' => $workflowInstance,
            'workflowTransition' => $workflowInstance?->currentState, // placeholder if needed
            'actor' => $execution->actor_id ? \App\Models\User::find($execution->actor_id) : null,
            'businessEntityType' => $execution->business_entity_type,
            'businessEntityId' => $execution->business_entity_id,
            'metadata' => $execution->metadata ?? [],
        ]);
    }

    public function generateIdempotencyKey(ActionDefinition $definition, ActionContext $context): string
    {
        return hash('sha256', implode('|', [
            $context->workflowInstance?->id,
            $context->workflowTransition?->id,
            $context->approvalInstance?->id,
            $definition->getId(),
            $definition->getVersion(),
        ]));
    }
}
