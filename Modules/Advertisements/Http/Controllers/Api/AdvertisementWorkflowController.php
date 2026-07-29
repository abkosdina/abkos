<?php

namespace Modules\Advertisements\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Advertisements\DTO\ApproveAdvertisementDTO;
use Modules\Shared\Base\BaseController;
use Modules\Advertisements\DTO\ArchiveAdvertisementDTO;
use Modules\Advertisements\DTO\CorrectionRequestDTO;
use Modules\Advertisements\DTO\MarkAsSoldDTO;
use Modules\Advertisements\DTO\PauseAdvertisementDTO;
use Modules\Advertisements\DTO\PublishAdvertisementDTO;
use Modules\Advertisements\DTO\RejectAdvertisementDTO;
use Modules\Advertisements\DTO\RestoreAdvertisementDTO;
use Modules\Advertisements\DTO\ResumeAdvertisementDTO;
use Modules\Advertisements\DTO\SubmitAdvertisementDTO;
use Modules\KYC\Services\KycAccessService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Modules\Advertisements\Http\Requests\ApproveAdvertisementRequest;
use Modules\Advertisements\Http\Requests\ArchiveAdvertisementRequest;
use Modules\Advertisements\Http\Requests\CorrectionRequestRequest;
use Modules\Advertisements\Http\Requests\PublishAdvertisementRequest;
use Modules\Advertisements\Http\Requests\RejectAdvertisementRequest;
use Modules\Advertisements\Http\Requests\SubmitAdvertisementRequest;
use Modules\Advertisements\Http\Resources\AdvertisementResource;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Services\AdvertisementWorkflowService;

/**
 * Advertisement Workflow Controller
 *
 * REST API endpoints for advertisement workflow actions.
 */
class AdvertisementWorkflowController extends BaseController
{
    public function __construct(
        protected AdvertisementWorkflowService $workflowService,
        protected ?KycAccessService $kycAccessService = null
    ) {}

    protected function assertKycApproved($user): void
    {
        if (! $this->kycAccessService || ! $user) {
            return;
        }

        if (! $this->kycAccessService->isApproved($user)) {
            throw new HttpException(
                403,
                $this->kycAccessService->getDenialReason($user) ?? 'KYC verification required.'
            );
        }
    }

    /**
     * Submit advertisement for review
     * POST /advertisements/{uuid}/submit
     */
    public function submit(
        string $uuid,
        SubmitAdvertisementRequest $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();
        $this->assertKycApproved($request->user());

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->submit($advertisement, SubmitAdvertisementDTO::fromArray($request->validated()))
        );
    }

    /**
     * Approve advertisement
     * POST /advertisements/{uuid}/approve
     */
    public function approve(
        string $uuid,
        ApproveAdvertisementRequest $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->approve($advertisement, ApproveAdvertisementDTO::fromArray($request->validated()))
        );
    }

    /**
     * Reject advertisement
     * POST /advertisements/{uuid}/reject
     */
    public function reject(
        string $uuid,
        RejectAdvertisementRequest $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->reject($advertisement, RejectAdvertisementDTO::fromArray($request->validated()))
        );
    }

    /**
     * Request correction
     * POST /advertisements/{uuid}/correction
     */
    public function correction(
        string $uuid,
        CorrectionRequestRequest $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->requestCorrection($advertisement, CorrectionRequestDTO::fromArray($request->validated()))
        );
    }

    /**
     * Publish advertisement
     * POST /advertisements/{uuid}/publish
     */
    public function publish(
        string $uuid,
        PublishAdvertisementRequest $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->publish($advertisement, PublishAdvertisementDTO::fromArray($request->validated()))
        );
    }

    /**
     * Pause advertisement
     * POST /advertisements/{uuid}/pause
     */
    public function pause(
        string $uuid,
        Request $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->pause($advertisement, PauseAdvertisementDTO::fromArray($request->only(['reason', 'comment', 'metadata'])))
        );
    }

    /**
     * Resume advertisement
     * POST /advertisements/{uuid}/resume
     */
    public function resume(
        string $uuid,
        Request $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->resume($advertisement, ResumeAdvertisementDTO::fromArray($request->only(['reason', 'comment', 'metadata'])))
        );
    }

    /**
     * Archive advertisement
     * POST /advertisements/{uuid}/archive
     */
    public function archive(
        string $uuid,
        ArchiveAdvertisementRequest $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->archive($advertisement, ArchiveAdvertisementDTO::fromArray($request->validated()))
        );
    }

    /**
     * Restore advertisement
     * POST /advertisements/{uuid}/restore
     */
    public function restore(
        string $uuid,
        Request $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->restore($advertisement, RestoreAdvertisementDTO::fromArray($request->only(['restore_to_state', 'reason', 'comment', 'metadata'])))
        );
    }

    /**
     * Mark advertisement as sold
     * POST /advertisements/{uuid}/sold
     */
    public function sold(
        string $uuid,
        Request $request
    ): JsonResponse {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return $this->handleWorkflowAction(
            $advertisement,
            fn () => $this->workflowService->markAsSold($advertisement, MarkAsSoldDTO::fromArray($request->only(['reason', 'comment', 'metadata'])))
        );
    }

    protected function handleWorkflowAction(Advertisement $advertisement, callable $action): JsonResponse
    {
        $response = $action();

        if (! $response->success) {
            return response()->json([
                'success' => false,
                'message' => $response->message,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $response->message,
            'data' => [
                'advertisement' => new AdvertisementResource($advertisement),
                'old_state' => $response->old_state,
                'new_state' => $response->new_state,
            ],
        ], 200);
    }

    /**
     * Get workflow state info
     * GET /advertisements/{uuid}/workflow-state
     */
    public function getWorkflowState(string $uuid): JsonResponse
    {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'uuid' => $advertisement->uuid,
                'current_state' => $advertisement->status->value,
                'state_label' => $advertisement->status->value,
                'created_at' => $advertisement->created_at,
                'updated_at' => $advertisement->updated_at,
            ],
        ], 200);
    }
}
