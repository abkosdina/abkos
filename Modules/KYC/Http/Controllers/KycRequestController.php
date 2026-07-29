<?php

namespace Modules\KYC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\KYC\Http\Requests\ApproveKycRequest;
use Modules\KYC\Http\Requests\RejectKycRequest;
use Modules\KYC\Http\Requests\RequestKycCorrectionRequest;
use Modules\KYC\Http\Requests\StoreKycProfileRequest;
use Modules\KYC\Http\Requests\StoreKycRequest;
use Modules\KYC\Http\Requests\SubmitKycForReviewRequest;
use Modules\KYC\Http\Requests\UpdateKycRequest;
use Modules\KYC\Http\Requests\UploadKycDocumentRequest;
use Modules\KYC\Http\Resources\KycDocumentResource;
use Modules\KYC\Http\Resources\KycRequestResource;
use Modules\KYC\Models\KycDocument;use Modules\KYC\Models\KycRequest;use Modules\KYC\Services\KycService;
use Modules\KYC\Services\KycVerificationService;
use Modules\Shared\Base\BaseController;

class KycRequestController extends BaseController
{
    public function __construct(
        protected KycService $kycService,
        protected KycVerificationService $kycVerificationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => KycRequestResource::collection($this->kycService->list($request->all())),
            'message' => 'KYC requests retrieved successfully.',
        ]);
    }

    public function store(StoreKycRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => KycRequestResource::make($this->kycService->create($request->validated())),
            'message' => 'KYC request created successfully.',
        ]);
    }

    public function show($kycRequest): JsonResponse
    {
        $request = $this->kycService->find($kycRequest);

        if (!$request) {
            abort(404, 'KYC request not found.');
        }

        return response()->json([
            'success' => true,
            'data' => KycRequestResource::make($request),
            'message' => 'KYC request retrieved successfully.',
        ]);
    }

    public function update(UpdateKycRequest $request, $kycRequest): JsonResponse
    {
        $requestEntity = $this->kycService->find($kycRequest);

        if (!$requestEntity) {
            abort(404, 'KYC request not found.');
        }

        $this->kycService->update($kycRequest, $request->validated());

        return response()->json([
            'success' => true,
            'data' => KycRequestResource::make($this->kycService->find($kycRequest)),
            'message' => 'KYC request updated successfully.',
        ]);
    }

    public function submit(SubmitKycForReviewRequest $request, $kycRequest): JsonResponse
    {
        $kycRequest = $this->kycService->find($kycRequest);

        if (!$kycRequest) {
            abort(404, 'KYC request not found.');
        }

        $this->authorize('submit', $kycRequest);

        $workflowInstance = $this->kycVerificationService->submitForReview($kycRequest, $request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'workflow_instance_id' => $workflowInstance->id,
                'kyc_request' => KycRequestResource::make($this->kycService->find($kycRequest->id)),
            ],
            'message' => 'KYC request submitted for review.',
        ]);
    }

    public function approve(ApproveKycRequest $request, $kycRequest): JsonResponse
    {
        $kycRequest = $this->kycService->find($kycRequest);

        if (!$kycRequest) {
            abort(404, 'KYC request not found.');
        }

        $this->authorize('approve', $kycRequest);

        $this->kycVerificationService->approve($kycRequest, $request->user(), $request->validated()['comment'] ?? null);

        return response()->json([
            'success' => true,
            'data' => KycRequestResource::make($this->kycService->find($kycRequest->id)),
            'message' => 'KYC request approved successfully.',
        ]);
    }

    public function reject(RejectKycRequest $request, $kycRequest): JsonResponse
    {
        $kycRequest = $this->kycService->find($kycRequest);

        if (!$kycRequest) {
            abort(404, 'KYC request not found.');
        }

        $this->authorize('reject', $kycRequest);

        $this->kycVerificationService->reject(
            $kycRequest,
            $request->user(),
            $request->validated()['reason'],
            $request->validated()['comment'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => KycRequestResource::make($this->kycService->find($kycRequest->id)),
            'message' => 'KYC request rejected successfully.',
        ]);
    }

    public function requestCorrection(RequestKycCorrectionRequest $request, $kycRequest): JsonResponse
    {
        $kycRequest = $this->kycService->find($kycRequest);

        if (!$kycRequest) {
            abort(404, 'KYC request not found.');
        }

        $this->authorize('requestCorrection', $kycRequest);

        $this->kycVerificationService->requestCorrection(
            $kycRequest,
            $request->user(),
            $request->validated()['reason'],
            $request->validated()['comment'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => KycRequestResource::make($this->kycService->find($kycRequest->id)),
            'message' => 'KYC correction request recorded successfully.',
        ]);
    }

    public function uploadDocument(UploadKycDocumentRequest $request, $kycRequest): JsonResponse
    {
        $kycRequest = $this->kycService->find($kycRequest);

        if (!$kycRequest) {
            abort(404, 'KYC request not found.');
        }

        $this->authorize('uploadDocument', $kycRequest);

        $document = $this->kycVerificationService->uploadDocument(
            $kycRequest,
            $request->validated()['document_type'],
            $request->file('file'),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => KycDocumentResource::make($document),
            'message' => 'KYC document uploaded successfully.',
        ]);
    }

    public function downloadDocument($kycRequest, KycDocument $document): JsonResponse
    {
        $kycRequest = $this->kycService->find($kycRequest);

        if (!$kycRequest || $document->kyc_request_id !== $kycRequest->id) {
            abort(404);
        }

        $this->authorize('downloadDocument', $kycRequest);

        return response()->json([
            'success' => true,
            'data' => $this->kycVerificationService->downloadDocument($document, request()->user()),
            'message' => 'KYC document download initiated successfully.',
        ]);
    }

    public function saveProfile(StoreKycProfileRequest $request, $kycRequest): JsonResponse
    {
        $kycRequest = $this->kycService->find($kycRequest);

        if (!$kycRequest) {
            abort(404, 'KYC request not found.');
        }

        $this->authorize('update', $kycRequest);

        return response()->json([
            'success' => true,
            'data' => $this->kycService->updateProfile($kycRequest, $request->validated()),
            'message' => 'KYC profile saved successfully.',
        ]);
    }
}
