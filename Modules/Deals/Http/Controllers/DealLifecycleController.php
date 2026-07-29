<?php

namespace Modules\Deals\Http\Controllers;

use App\Models\WorkflowTransition;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Deals\Http\Requests\DealActionRequest;
use Modules\Deals\Repositories\Interfaces\DealRepositoryInterface;
use Modules\Deals\Services\DealWorkflowService;
use Modules\Deals\Events\DealCancelled;
use Modules\Deals\Events\DealExpired;
use Modules\Deals\Events\DealDisputed;
use Modules\Deals\Events\DealClosed;
use Modules\Deals\Events\DealCompleted;

class DealLifecycleController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected DealRepositoryInterface $deals,
        protected DealWorkflowService $workflowService,
        protected WorkflowEngine $workflowEngine
    ) {
    }

    protected function transitionDeal(string $uuid, string $transitionKey, DealActionRequest $request, string $ability, $eventClass): JsonResponse
    {
        $deal = $this->deals->findByUuid($uuid);
        if (! $deal) {
            return response()->json(['success' => false, 'message' => 'Deal not found'], 404);
        }

        $this->authorize($ability, $deal);

        // Ensure workflow instance exists
        $instance = $deal->workflowInstance ?? $this->workflowService->createDealWorkflow($deal);

        $transition = \App\Models\WorkflowTransition::query()
            ->where('workflow_definition_id', $instance->workflow_definition_id)
            ->where('key', $transitionKey)
            ->first();

        if (! $transition) {
            return response()->json(['success' => false, 'message' => 'Transition not found'], 422);
        }

        $payload = [
            'reason' => $request->input('reason'),
            'metadata' => $request->input('metadata'),
            'idempotency_key' => $request->input('idempotency_key') ?? $request->header('Idempotency-Key'),
        ];

        try {
            $step = $this->workflowEngine->transition($instance, $transition, $payload);

            // dispatch event
            if ($eventClass) {
                event(new $eventClass($deal, ['step_id' => $step->id]));
            }

            return response()->json(['success' => true, 'data' => ['new_state' => $step->toState->key]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(string $uuid, DealActionRequest $request): JsonResponse
    {
        return $this->transitionDeal($uuid, 'cancel', $request, 'cancel', DealCancelled::class);
    }

    public function expire(string $uuid, DealActionRequest $request): JsonResponse
    {
        return $this->transitionDeal($uuid, 'expire', $request, 'expire', DealExpired::class);
    }

    public function dispute(string $uuid, DealActionRequest $request): JsonResponse
    {
        return $this->transitionDeal($uuid, 'dispute', $request, 'dispute', DealDisputed::class);
    }

    public function close(string $uuid, DealActionRequest $request): JsonResponse
    {
        return $this->transitionDeal($uuid, 'close', $request, 'close', DealClosed::class);
    }

    public function complete(string $uuid, DealActionRequest $request): JsonResponse
    {
        return $this->transitionDeal($uuid, 'complete', $request, 'complete', DealCompleted::class);
    }
}
