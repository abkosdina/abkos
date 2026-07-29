<?php

namespace Modules\Advertisements\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Advertisements\Adapters\AdvertisementWorkflowAdapter;
use Modules\Advertisements\DTO\ApproveAdvertisementDTO;
use Modules\Advertisements\DTO\ArchiveAdvertisementDTO;
use Modules\Advertisements\DTO\CorrectionRequestDTO;
use Modules\Advertisements\DTO\MarkAsSoldDTO;
use Modules\Advertisements\DTO\PauseAdvertisementDTO;
use Modules\Advertisements\DTO\PublishAdvertisementDTO;
use Modules\Advertisements\DTO\RejectAdvertisementDTO;
use Modules\Advertisements\DTO\RestoreAdvertisementDTO;
use Modules\Advertisements\DTO\ResumeAdvertisementDTO;
use Modules\Advertisements\DTO\SubmitAdvertisementDTO;
use App\Models\User;
use Modules\Advertisements\DTO\WorkflowActionResponseDTO;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;

/**
 * Advertisement Workflow Service
 *
 * High-level workflow orchestration service.
 * Coordinates workflow operations, validation, and audit logging.
 */
class AdvertisementWorkflowService
{
    protected AdvertisementWorkflowAdapter $workflowAdapter;

    public function __construct(AdvertisementWorkflowAdapter $workflowAdapter)
    {
        $this->workflowAdapter = $workflowAdapter;
    }

    /**
     * Submit advertisement for review
     */
    public function submit(
        Advertisement $advertisement,
        SubmitAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'submit',
            $dto,
            'Advertisement submitted successfully',
            'Failed to submit advertisement',
            'Advertisement submitted',
            ['uuid' => $advertisement->uuid]
        );
    }

    /**
     * Approve advertisement
     */
    public function approve(
        Advertisement $advertisement,
        ApproveAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'approve',
            $dto,
            'Advertisement approved successfully',
            'Failed to approve advertisement',
            'Advertisement approved',
            ['reason' => $dto->reason ?? null]
        );
    }

    /**
     * Reject advertisement
     */
    public function reject(
        Advertisement $advertisement,
        RejectAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'reject',
            $dto,
            'Advertisement rejected successfully',
            'Failed to reject advertisement',
            'Advertisement rejected',
            ['reason' => $dto->reason]
        );
    }

    /**
     * Request correction
     */
    public function requestCorrection(
        Advertisement $advertisement,
        CorrectionRequestDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'correction',
            $dto,
            'Correction requested successfully',
            'Failed to request correction',
            'Correction requested',
            ['reason' => $dto->reason]
        );
    }

    /**
     * Publish advertisement
     */
    public function publish(
        Advertisement $advertisement,
        PublishAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'publish',
            $dto,
            'Advertisement published successfully',
            'Failed to publish advertisement',
            'Advertisement published'
        );
    }

    /**
     * Pause advertisement
     */
    public function pause(
        Advertisement $advertisement,
        PauseAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'pause',
            $dto,
            'Advertisement paused successfully',
            'Failed to pause advertisement',
            'Advertisement paused'
        );
    }

    /**
     * Resume advertisement
     */
    public function resume(
        Advertisement $advertisement,
        ResumeAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'resume',
            $dto,
            'Advertisement resumed successfully',
            'Failed to resume advertisement',
            'Advertisement resumed'
        );
    }

    /**
     * Archive advertisement
     */
    public function archive(
        Advertisement $advertisement,
        ArchiveAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'archive',
            $dto,
            'Advertisement archived successfully',
            'Failed to archive advertisement',
            'Advertisement archived'
        );
    }

    /**
     * Restore advertisement
     */
    public function restore(
        Advertisement $advertisement,
        RestoreAdvertisementDTO $dto
    ): WorkflowActionResponseDTO {
        $restoreToState = AdvertisementStatus::tryFrom($dto->restore_to_state ?? 'Draft') ?? AdvertisementStatus::Draft;

        return $this->transitionWithPayload(
            $advertisement,
            'restore',
            array_merge($dto->toArray(), ['restore_to_state' => $restoreToState]),
            'Advertisement restored successfully',
            'Failed to restore advertisement',
            'Advertisement restored',
            ['restored_to' => $restoreToState->value]
        );
    }

    /**
     * Mark advertisement as sold
     */
    public function markAsSold(
        Advertisement $advertisement,
        MarkAsSoldDTO $dto
    ): WorkflowActionResponseDTO {
        return $this->transitionForDto(
            $advertisement,
            'sold',
            $dto,
            'Advertisement marked as sold successfully',
            'Failed to mark advertisement as sold',
            'Advertisement marked as sold'
        );
    }

    /**
     * Check authorization
     */

    protected function transitionForDto(
        Advertisement $advertisement,
        string $action,
        object $dto,
        string $successMessage,
        string $failureMessage,
        string $logMessage,
        array $extraLogContext = []
    ): WorkflowActionResponseDTO {
        return $this->transitionWithPayload(
            $advertisement,
            $action,
            $dto->toArray(),
            $successMessage,
            $failureMessage,
            $logMessage,
            $extraLogContext
        );
    }

    protected function transitionWithPayload(
        Advertisement $advertisement,
        string $action,
        array $payload,
        string $successMessage,
        string $failureMessage,
        string $logMessage,
        array $extraLogContext = []
    ): WorkflowActionResponseDTO {
        return $this->performTransition(
            $advertisement,
            $payload,
            fn (Advertisement $item, array $requestPayload) => $this->dispatchTransition($item, $action, $requestPayload),
            $successMessage,
            $failureMessage,
            $logMessage,
            $extraLogContext
        );
    }

    protected function dispatchTransition(Advertisement $advertisement, string $action, array $payload)
    {
        return match ($action) {
            'submit' => $this->workflowAdapter->submit($advertisement, $payload),
            'approve' => $this->workflowAdapter->approve($advertisement, $payload),
            'reject' => $this->workflowAdapter->reject($advertisement, $payload),
            'correction' => $this->workflowAdapter->requestCorrection($advertisement, $payload),
            'publish' => $this->workflowAdapter->publish($advertisement, $payload),
            'pause' => $this->workflowAdapter->pause($advertisement, $payload),
            'resume' => $this->workflowAdapter->resume($advertisement, $payload),
            'archive' => $this->workflowAdapter->archive($advertisement, $payload),
            'restore' => $this->workflowAdapter->restore($advertisement, $payload),
            'expire' => $this->workflowAdapter->expire($advertisement, $payload),
            'sold' => $this->workflowAdapter->markAsSold($advertisement, $payload),
            default => throw new \InvalidArgumentException("Unsupported workflow action: {$action}"),
        };
    }

    protected function performTransition(
        Advertisement $advertisement,
        array $payload,
        callable $transition,
        string $successMessage,
        string $failureMessage,
        string $logMessage,
        array $extraLogContext = []
    ): WorkflowActionResponseDTO {
        return DB::transaction(function () use ($advertisement, $payload, $transition, $successMessage, $failureMessage, $logMessage, $extraLogContext) {
            try {
                $oldState = $advertisement->status->value;
                $adapterResponse = $transition($advertisement, $payload);

                if (!$adapterResponse->success) {
                    Log::error($failureMessage . ': ' . ($adapterResponse->message ?: 'No message'), array_merge([
                        'advertisement_id' => $advertisement->id,
                        'uuid' => $advertisement->uuid,
                    ], $extraLogContext));

                    return $this->failResponse($adapterResponse->message ?: $failureMessage);
                }

                $advertisement->refresh();

                Log::info($logMessage, array_merge([
                    'advertisement_id' => $advertisement->id,
                    'old_state' => $oldState,
                    'new_state' => $advertisement->status->value,
                ], $extraLogContext));

                return $this->successResponse(
                    $successMessage,
                    $oldState,
                    $advertisement->status->value,
                    $advertisement
                );
            } catch (\Exception $e) {
                Log::error($failureMessage . ': ' . $e->getMessage(), array_merge([
                    'advertisement_id' => $advertisement->id,
                    'uuid' => $advertisement->uuid,
                ], $extraLogContext));

                return $this->failResponse($e->getMessage());
            }
        });
    }

    /**
     * Create success response
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
        $response->advertisement = $this->formatAdvertisementData($advertisement);

        return $response;
    }

    /**
     * Create failure response
     */
    protected function failResponse(string $message): WorkflowActionResponseDTO
    {
        $response = new WorkflowActionResponseDTO();
        $response->success = false;
        $response->message = $message;

        return $response;
    }

    /**
     * Format advertisement data for response
     */
    protected function formatAdvertisementData(Advertisement $advertisement): array
    {
        return [
            'id' => $advertisement->id,
            'uuid' => $advertisement->uuid,
            'title' => $advertisement->title,
            'status' => $advertisement->status->value,
            'created_at' => $advertisement->created_at,
            'updated_at' => $advertisement->updated_at,
        ];
    }

    protected function authorize(string $action, Advertisement $advertisement, ?User $user): bool
    {
        // For now, workflows are authorized by route/controller guards.
        // If additional permission checks are needed, implement them here.
        return true;
    }

    public function applyTransition(?User $user, Advertisement $advertisement, string $action, array $payload = []): bool
    {
        $this->authorize($action, $advertisement, $user);

        $action = strtolower($action);

        try {
            return $this->dispatchTransition($advertisement, $action, $payload)->success;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
