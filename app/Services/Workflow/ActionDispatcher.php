<?php

namespace App\Services\Workflow;

use App\Events\Workflow\WorkflowCancelled;
use App\Events\Workflow\WorkflowCompleted;
use App\Events\Workflow\WorkflowTransitioned;
use App\Jobs\ExecuteWorkflowActionJob;
use App\Models\WorkflowInstance;
use App\Models\WorkflowTransition;
use App\Models\WorkflowInstanceStep;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Workflow\Events\ApprovalApproved;
use Modules\Workflow\Events\ApprovalCompleted;
use Modules\Workflow\Events\ApprovalRejected;
use Modules\Workflow\Events\ApprovalReturnedForCorrection;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalDecision;
use Throwable;

class ActionDispatcher
{
    public function __construct(
        protected ActionTriggerResolver $triggerResolver,
        protected ActionExecutionService $executionService,
        protected ActionContextBuilder $contextBuilder
    ) {
    }

    public function handleWorkflowTransitioned(WorkflowTransitioned $event): void
    {
        $actor = Auth::user();
        $transition = $event->step->transition ?? null;
        $context = $this->contextBuilder->buildForWorkflow(
            $event->instance,
            $transition,
            $event->step,
            $actor,
            null,
            []
        );

        $this->dispatchActions('workflow.transitioned', $event->instance, $transition, $event->step, $context);
    }

    public function handleWorkflowCompleted(WorkflowCompleted $event): void
    {
        $actor = Auth::user();
        $context = $this->contextBuilder->buildForWorkflow($event->instance, null, null, $actor, null, []);

        $this->dispatchActions('workflow.completed', $event->instance, null, null, $context);
    }

    public function handleWorkflowCancelled(WorkflowCancelled $event): void
    {
        $actor = Auth::user();
        $context = $this->contextBuilder->buildForWorkflow($event->instance, null, null, $actor, null, []);

        $this->dispatchActions('workflow.cancelled', $event->instance, null, null, $context);
    }

    public function handleApprovalApproved(ApprovalApproved $event): void
    {
        $context = $this->contextBuilder->buildForApproval(
            $event->approvalInstance,
            $event->approvalInstanceStep,
            $event->approvalDecision,
            $event->actor,
            null,
            []
        );

        $this->dispatchApprovalActions('approval.approved', $event->approvalInstance, $event->approvalInstanceStep, $event->approvalDecision, $context);
    }

    public function handleApprovalRejected(ApprovalRejected $event): void
    {
        $context = $this->contextBuilder->buildForApproval(
            $event->approvalInstance,
            $event->approvalInstanceStep,
            $event->approvalDecision,
            $event->actor,
            null,
            []
        );

        $this->dispatchApprovalActions('approval.rejected', $event->approvalInstance, $event->approvalInstanceStep, $event->approvalDecision, $context);
    }

    public function handleApprovalReturnedForCorrection(ApprovalReturnedForCorrection $event): void
    {
        $context = $this->contextBuilder->buildForApproval(
            $event->approvalInstance,
            $event->approvalInstanceStep,
            $event->approvalDecision,
            $event->actor,
            null,
            []
        );

        $this->dispatchApprovalActions('approval.returned_for_correction', $event->approvalInstance, $event->approvalInstanceStep, $event->approvalDecision, $context);
    }

    public function handleApprovalCompleted(ApprovalCompleted $event): void
    {
        $context = $this->contextBuilder->buildForApproval(
            $event->approvalInstance,
            $event->approvalInstanceStep,
            $event->approvalDecision,
            $event->actor,
            null,
            []
        );

        $this->dispatchApprovalActions('approval.completed', $event->approvalInstance, $event->approvalInstanceStep, $event->approvalDecision, $context);
    }

    protected function dispatchActions(
        string $eventName,
        WorkflowInstance $workflowInstance,
        ?WorkflowTransition $transition,
        ?WorkflowInstanceStep $step,
        ActionContext $context
    ): void {
        $definitions = $this->triggerResolver->resolve($eventName, $workflowInstance, $transition, $step);

        foreach ($definitions as $definition) {
            $this->dispatchAction($definition, $context);
        }
    }

    protected function dispatchApprovalActions(
        string $eventName,
        ApprovalInstance $approvalInstance,
        ?ApprovalInstanceStep $approvalStep,
        ?ApprovalDecision $approvalDecision,
        ActionContext $context
    ): void {
        $workflowInstance = $approvalInstance->workflowInstance;
        if (! $workflowInstance) {
            return;
        }

        $definitions = $this->triggerResolver->resolve($eventName, $workflowInstance, null, null);
        foreach ($definitions as $definition) {
            $this->dispatchAction($definition, $context);
        }
    }

    protected function dispatchAction(ActionDefinition $definition, ActionContext $context): void
    {
        if ($definition->isBlocking()) {
            $result = $this->executionService->execute($definition, $context);
            if (! $result->success && $definition->getFailurePolicy() === 'stop') {
                throw new \RuntimeException(
                    sprintf('Blocking workflow action [%s] failed: %s', $definition->getKey(), $result->message)
                );
            }

            return;
        }

        $execution = $this->executionService->createPendingExecution($definition, $context);
        ExecuteWorkflowActionJob::dispatch($execution->id);
    }
}
