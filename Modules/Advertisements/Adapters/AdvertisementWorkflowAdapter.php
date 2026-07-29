<?php

namespace Modules\Advertisements\Adapters;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Services\Workflow\WorkflowEngine;
use Database\Seeders\AdvertisementWorkflowDefinitionSeeder;
use Illuminate\Support\Facades\DB;
use Modules\Advertisements\DTO\WorkflowActionResponseDTO;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Models\AdvertisementWorkflowAudit;

/**
 * AdvertisementWorkflowAdapter
 * 
 * Backward compatibility adapter that translates Advertisement-specific
 * workflow calls to the generic Workflow Engine.
 * 
 * This allows existing Advertisement workflow code to continue working
 * while internally using the new generic workflow system.
 * 
 * Example:
 *   Old API: $service->submit($advertisement, $dto)
 *   New API: Internally calls $workflowEngine->transition(...)
 */
class AdvertisementWorkflowAdapter
{
    protected WorkflowEngine $workflowEngine;
    protected ?WorkflowDefinition $definition = null;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    /**
     * Get or create the Advertisement workflow definition
     */
    protected function getWorkflowDefinition(): WorkflowDefinition
    {
        if ($this->definition) {
            return $this->definition;
        }

        $this->definition = $this->workflowEngine->getDefinition('advertisement.approval');

        if (!$this->definition) {
            app(AdvertisementWorkflowDefinitionSeeder::class)->run();
            $this->definition = $this->workflowEngine->getDefinition('advertisement.approval');
        }

        if (!$this->definition) {
            throw new \Exception(
                'Advertisement workflow definition could not be created.'
            );
        }

        return $this->definition;
    }

    /**
     * Ensure a workflow instance exists for an advertisement.
     */
    public function ensureWorkflowInstance(Advertisement $advertisement): WorkflowInstance
    {
        return $this->getOrCreateInstance($advertisement);
    }

    /**
     * Get or create workflow instance for an advertisement
     */
    protected function getOrCreateInstance(Advertisement $advertisement): WorkflowInstance
    {
        $definition = $this->getWorkflowDefinition();

        // Check if instance already exists
        $instance = $this->workflowEngine->getInstance('Advertisement', $advertisement->id, $definition->id);

        if ($instance) {
            return $this->syncInstanceState($advertisement, $instance, $definition);
        }

        // Create a new workflow instance
        $instance = $this->workflowEngine->start(
            $definition,
            'Advertisement',
            $advertisement->id,
            ['migrated_from_old_system' => false]
        );

        return $this->syncInstanceState($advertisement, $instance, $definition);
    }

    protected function syncInstanceState(Advertisement $advertisement, WorkflowInstance $instance, WorkflowDefinition $definition): WorkflowInstance
    {
        $desiredStateKey = $this->mapStatusToWorkflowStateKey($advertisement->status);
        $currentStateKey = $instance->currentState?->key;

        if ($currentStateKey === $desiredStateKey) {
            return $instance;
        }

        $desiredState = $definition->states()->where('key', $desiredStateKey)->first();
        if (! $desiredState) {
            return $instance;
        }

        $instance->current_state_id = $desiredState->id;
        $instance->status = $desiredState->is_final ? 'completed' : 'active';
        $instance->completed_at = $desiredState->is_final ? now() : null;
        $instance->save();
        $instance->refresh();

        return $instance;
    }

    protected function mapStatusToWorkflowStateKey(AdvertisementStatus|string $status): string
    {
        $statusValue = $status instanceof AdvertisementStatus ? $status->value : $status;

        return match ($statusValue) {
            AdvertisementStatus::Draft->value => 'draft',
            AdvertisementStatus::PendingReview->value => 'pending_review',
            AdvertisementStatus::NeedCorrection->value => 'needs_correction',
            AdvertisementStatus::Rejected->value => 'rejected',
            AdvertisementStatus::Approved->value => 'approved',
            AdvertisementStatus::Published->value => 'published',
            AdvertisementStatus::Paused->value => 'paused',
            AdvertisementStatus::Expired->value => 'expired',
            AdvertisementStatus::Sold->value => 'sold',
            AdvertisementStatus::Archived->value => 'archived',
            AdvertisementStatus::Deleted->value => 'deleted',
            default => 'draft',
        };
    }

    /**
     * Submit advertisement for review
     * 
     * Maps to: Draft → PendingReview transition
     */
    public function submit(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'submit',
            $payload,
            null,
            'Advertisement submitted successfully',
            'submit'
        );
    }

    /**
     * Approve advertisement
     * 
     * Maps to: PendingReview → Approved transition
     */
    public function approve(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'approve',
            $payload,
            null,
            'Advertisement approved successfully',
            'approve'
        );
    }

    /**
     * Reject advertisement
     * 
     * Maps to: PendingReview → Rejected transition
     */
    public function reject(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'reject',
            $payload,
            null,
            'Advertisement rejected successfully',
            'reject'
        );
    }

    /**
     * Request correction
     * 
     * Maps to: PendingReview → NeedsCorrection transition
     */
    public function requestCorrection(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'request_correction',
            $payload,
            null,
            'Correction requested successfully',
            'request_correction'
        );
    }

    /**
     * Publish advertisement
     * 
     * Maps to: Approved → Published transition
     */
    public function publish(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'publish',
            $payload,
            function (Advertisement $item): void {
                $item->published_at = now();
                $item->save();
            },
            'Advertisement published successfully',
            'publish'
        );
    }

    /**
     * Pause advertisement
     * 
     * Maps to: Published → Paused transition
     */
    public function pause(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'pause',
            $payload,
            null,
            'Advertisement paused successfully',
            'pause'
        );
    }

    /**
     * Resume advertisement
     * 
     * Maps to: Paused → Published transition
     */
    public function resume(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'resume',
            $payload,
            null,
            'Advertisement resumed successfully',
            'resume'
        );
    }

    /**
     * Archive advertisement
     * 
     * Maps to: Various → Archived transition
     */
    public function archive(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'archive',
            $payload,
            null,
            'Advertisement archived successfully',
            'archive'
        );
    }

    /**
     * Restore advertisement
     * 
     * Maps to: Archived → Draft transition
     */
    public function restore(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'restore',
            $payload,
            null,
            'Advertisement restored successfully',
            'restore'
        );
    }

    /**
     * Expire advertisement
     * 
     * Maps to: Published → Expired transition
     */
    public function expire(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'expire',
            $payload,
            null,
            'Advertisement expired successfully',
            'expire'
        );
    }

    /**
     * Mark advertisement as sold
     * 
     * Maps to: Published → Sold transition
     */
    public function markAsSold(
        Advertisement $advertisement,
        array $payload = []
    ): WorkflowActionResponseDTO {
        return $this->transitionAdvertisement(
            $advertisement,
            'mark_sold',
            $payload,
            null,
            'Advertisement marked as sold successfully',
            'mark_sold'
        );
    }

    protected function transitionAdvertisement(
        Advertisement $advertisement,
        string $transitionKey,
        array $payload = [],
        ?callable $beforeTransition = null,
        ?string $successMessage = null,
        ?string $auditAction = null
    ): WorkflowActionResponseDTO {
        return DB::transaction(function () use ($advertisement, $transitionKey, $payload, $beforeTransition, $successMessage, $auditAction): WorkflowActionResponseDTO {
            try {
                $instance = $this->getOrCreateInstance($advertisement);
                $definition = $this->getWorkflowDefinition();
                $transition = $this->resolveTransition($definition, $instance, $transitionKey);

                $oldState = $instance->currentState->key;

                if ($beforeTransition) {
                    $beforeTransition($advertisement, $instance, $payload);
                }

                $this->workflowEngine->transition($instance, $transition, $payload);

                $instance->refresh();
                $this->syncAdvertisementStatus($advertisement, $instance);

                $this->recordAudit(
                    $advertisement,
                    $oldState,
                    $instance->currentState->key,
                    $auditAction ?? $transitionKey,
                    $payload
                );

                return $this->successResponse(
                    $successMessage ?? $this->defaultSuccessMessage($transitionKey),
                    $oldState,
                    $instance->currentState->key,
                    $advertisement
                );
            } catch (\Exception $e) {
                return $this->failResponse($e->getMessage());
            }
        });
    }

    protected function resolveTransition(
        \App\Models\WorkflowDefinition $definition,
        WorkflowInstance $instance,
        string $transitionKey
    ) {
        if ($transitionKey === 'archive') {
            $targetState = $definition->states()->where('key', 'archived')->first();

            if (! $targetState) {
                throw new \Exception('Archived workflow state not found');
            }

            $transition = $definition->transitions()
                ->where('to_state_id', $targetState->id)
                ->where('from_state_id', $instance->currentState->id)
                ->first();

            if (! $transition) {
                throw new \Exception('Cannot archive from current state');
            }

            return $transition;
        }

        return $definition->transitions()
            ->where('key', $transitionKey)
            ->firstOrFail();
    }

    protected function defaultSuccessMessage(string $transitionKey): string
    {
        return match ($transitionKey) {
            'submit' => 'Advertisement submitted successfully',
            'approve' => 'Advertisement approved successfully',
            'reject' => 'Advertisement rejected successfully',
            'request_correction' => 'Correction requested successfully',
            'publish' => 'Advertisement published successfully',
            'pause' => 'Advertisement paused successfully',
            'resume' => 'Advertisement resumed successfully',
            'restore' => 'Advertisement restored successfully',
            'expire' => 'Advertisement expired successfully',
            'mark_sold' => 'Advertisement marked as sold successfully',
            'archive' => 'Advertisement archived successfully',
            default => ucfirst(str_replace('_', ' ', $transitionKey)) . ' completed successfully',
        };
    }

    /**
     * Sync Advertisement status with WorkflowInstance state
     * 
     * This ensures the Advertisement.status field stays in sync
     * with the workflow instance state (for fast querying and backward compat)
     */
    protected function recordAudit(
        Advertisement $advertisement,
        string $oldState,
        string $newState,
        string $action,
        array $payload = []
    ): void {
        $oldStatus = $this->mapStateKeyToAdvertisementStatus($oldState);
        $newStatus = $this->mapStateKeyToAdvertisementStatus($newState);

        AdvertisementWorkflowAudit::create([
            'advertisement_uuid' => $advertisement->uuid,
            'old_state' => $oldStatus,
            'new_state' => $newStatus,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->roles()->first()?->name ?? 'unknown',
            'action' => $action,
            'reason' => $payload['reason'] ?? null,
            'comment' => $payload['comment'] ?? null,
            'ip' => request()->ip(),
            'device' => request()->userAgent(),
            'extra' => collect($payload)->except(['reason', 'comment'])->toArray(),
        ]);
    }

    protected function mapStateKeyToAdvertisementStatus(string $stateKey): string
    {
        return match ($stateKey) {
            'draft' => AdvertisementStatus::Draft->value,
            'pending_review' => AdvertisementStatus::PendingReview->value,
            'needs_correction' => AdvertisementStatus::NeedCorrection->value,
            'rejected' => AdvertisementStatus::Rejected->value,
            'approved' => AdvertisementStatus::Approved->value,
            'published' => AdvertisementStatus::Published->value,
            'paused' => AdvertisementStatus::Paused->value,
            'expired' => AdvertisementStatus::Expired->value,
            'sold' => AdvertisementStatus::Sold->value,
            'archived' => AdvertisementStatus::Archived->value,
            'deleted' => AdvertisementStatus::Deleted->value,
            default => ucfirst(str_replace('_', ' ', $stateKey))
        };
    }

    protected function syncAdvertisementStatus(
        Advertisement $advertisement,
        WorkflowInstance $instance
    ): void {
        // Map workflow state key to Advertisement status enum
        $stateKeyToStatus = [
            'draft' => 'Draft',
            'pending_review' => 'PendingReview',
            'needs_correction' => 'NeedCorrection',
            'rejected' => 'Rejected',
            'approved' => 'Approved',
            'published' => 'Published',
            'paused' => 'Paused',
            'expired' => 'Expired',
            'sold' => 'Sold',
            'archived' => 'Archived',
            'deleted' => 'Deleted',
        ];

        $newStatus = $stateKeyToStatus[$instance->currentState->key] ?? 'Draft';

        // Use Enum if available
        try {
            $advertisement->status = \Modules\Advertisements\Enums\AdvertisementStatus::from($newStatus);
        } catch (\ValueError) {
            $advertisement->status = $newStatus;
        }

        $advertisement->workflow_instance_id = $instance->id;
        $advertisement->save();
    }

    /**
     * Build success response
     */
    protected function successResponse(
        string $message,
        string $oldState,
        string $newState,
        Advertisement $advertisement
    ): WorkflowActionResponseDTO {
        $response = new WorkflowActionResponseDTO();
        $response->success = true;
        $response->message = $message;
        $response->old_state = $oldState;
        $response->new_state = $newState;
        $response->advertisement = $advertisement->toArray();
        return $response;
    }

    /**
     * Build failure response
     */
    protected function failResponse(string $message): WorkflowActionResponseDTO
    {
        $response = new WorkflowActionResponseDTO();
        $response->success = false;
        $response->message = $message;
        return $response;
    }
}
