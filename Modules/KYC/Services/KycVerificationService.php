<?php

namespace Modules\KYC\Services;

use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Documents\Services\HashService;
use Modules\Documents\Services\StorageService;
use Modules\KYC\Models\KycDocument;
use Modules\KYC\Models\KycIdentitySnapshot;
use Modules\KYC\Models\KycProfile;
use Modules\KYC\Models\KycRequest;
use Modules\KYC\Repositories\Interfaces\KycProfileRepositoryInterface;
use Modules\KYC\Repositories\Interfaces\KycRequestRepositoryInterface;
use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Models\ApprovalDefinition;

class KycVerificationService
{
    public function __construct(
        protected KycRequestRepositoryInterface $requestRepository,
        protected KycProfileRepositoryInterface $profileRepository,
        protected StorageService $storageService,
        protected HashService $hashService,
        protected WorkflowEngine $workflowEngine,
        protected ApprovalEngineInterface $approvalEngine,
    ) {
    }

    /**
     * Create a new KYC verification request
     */
    public function createVerification(User $user): KycRequest
    {
        return DB::transaction(function () use ($user) {
            $kyc = $this->requestRepository->create([
                'uuid' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'status' => 'draft',
                'priority' => 1,
            ]);

            // Record status history
            $this->recordStatusHistory($kyc, null, 'draft', $user, 'system', 'KYC verification created');

            return $kyc;
        });
    }

    /**
     * Save identity information for KYC
     */
    public function saveIdentity(KycRequest $kyc, array $data): KycProfile
    {
        return DB::transaction(function () use ($kyc, $data) {
            $profile = $this->profileRepository->findByUserId($kyc->user_id) 
                ?? $this->profileRepository->create([
                    'uuid' => Str::uuid()->toString(),
                    'user_id' => $kyc->user_id,
                ]);

            // Update profile with identity data
            $profileData = array_filter([
                'national_code' => $data['national_code'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            $this->profileRepository->update($profile->id, $profileData);

            return $this->profileRepository->find($profile->id);
        });
    }

    /**
     * Upload and store a KYC document securely
     */
    public function uploadDocument(
        KycRequest $kyc,
        string $documentType,
        UploadedFile $file,
        User $uploader
    ): KycDocument {
        return DB::transaction(function () use ($kyc, $documentType, $file, $uploader) {
            // Validate file
            $this->validateDocumentFile($file);

            // Generate secure filename (UUID-based)
            $uuid = Str::uuid()->toString();
            $extension = $file->getClientOriginalExtension();
            $secureFilename = "{$uuid}.{$extension}";

            // Store file securely in private storage
            $path = $this->storageService->putFile('kyc/documents', $file, $secureFilename);

            // Create document record with all metadata
            $document = KycDocument::create([
                'uuid' => $uuid,
                'kyc_request_id' => $kyc->id,
                'document_type' => $documentType,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_hash' => $this->hashService->generateFileHash($file),
                'document_status' => 'uploaded',
                'uploaded_by' => $uploader->id,
                'uploaded_at' => now(),
                'metadata' => [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);

            // Record audit trail
            $this->recordAuditTrail($kyc->user_id, 'kyc_document_uploaded', 'KYC Document Uploaded', [
                'document_id' => $document->id,
                'document_type' => $documentType,
                'file_size' => $file->getSize(),
                'original_filename' => $file->getClientOriginalName(),
            ]);

            return $document;
        });
    }

    /**
     * Submit KYC for review (creates workflow instance and triggers approval)
     */
    public function submitForReview(KycRequest $kyc, User $submitter): WorkflowInstance
    {
        return DB::transaction(function () use ($kyc, $submitter) {
            // Validate all required documents are present
            $this->validateRequiredDocuments($kyc);

            // Get or create KYC workflow definition
            $workflowDefinition = $this->getOrCreateKycWorkflowDefinition();

            // Create workflow instance
            $workflowInstance = $this->workflowEngine->start(
                $workflowDefinition,
                'KYC',
                $kyc->id,
                ['user_id' => $kyc->user_id]
            );

            // Link workflow instance to KYC
            $this->requestRepository->update($kyc->id, [
                'workflow_instance_id' => $workflowInstance->id,
                'status' => 'submitted',
                'started_at' => now(),
            ]);

            // Create identity snapshot for audit
            $this->createIdentitySnapshot($kyc);

            // Get approval definition and start approval
            $approvalDefinition = $this->getOrCreateKycApprovalDefinition($workflowDefinition);
            $this->approvalEngine->start($workflowInstance, $approvalDefinition);

            // Record status history
            $this->recordStatusHistory($kyc, 'draft', 'submitted', $submitter, 'user', 'KYC submitted for review');

            // Record audit trail
            $this->recordAuditTrail($kyc->user_id, 'kyc_submitted', 'KYC Submitted for Review', [
                'workflow_instance_id' => $workflowInstance->id,
            ]);

            return $workflowInstance;
        });
    }

    /**
     * Approve KYC verification
     */
    public function approve(KycRequest $kyc, User $reviewer, ?string $comment = null): void
    {
        DB::transaction(function () use ($kyc, $reviewer, $comment) {
            // Update KYC status
            $this->requestRepository->update($kyc->id, [
                'status' => 'approved',
                'completed_at' => now(),
            ]);

            // Update user status for quick access checks
            if (method_exists($kyc->user, 'update')) {
                $kyc->user->update(['kyc_status' => 'approved']);
            }

            // Record status history
            $this->recordStatusHistory($kyc, 'submitted', 'approved', $reviewer, 'reviewer', $comment);

            // Record audit trail
            $this->recordAuditTrail($kyc->user_id, 'kyc_approved', 'KYC Approved', [
                'reviewed_by' => $reviewer->id,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Reject KYC verification
     */
    public function reject(KycRequest $kyc, User $reviewer, string $reason, ?string $comment = null): void
    {
        DB::transaction(function () use ($kyc, $reviewer, $reason, $comment) {
            // Update KYC status
            $this->requestRepository->update($kyc->id, [
                'status' => 'rejected',
                'completed_at' => now(),
            ]);

            // Record rejection reason
            $kyc->rejections()->create([
                'uuid' => Str::uuid()->toString(),
                'operator_id' => $reviewer->id,
                'reason' => $reason,
                'comment' => $comment,
            ]);

            // Record status history
            $this->recordStatusHistory($kyc, 'submitted', 'rejected', $reviewer, 'reviewer', $comment ?? $reason);

            // Record audit trail
            $this->recordAuditTrail($kyc->user_id, 'kyc_rejected', 'KYC Rejected', [
                'reviewed_by' => $reviewer->id,
                'reason' => $reason,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Request correction on KYC
     */
    public function requestCorrection(KycRequest $kyc, User $reviewer, string $reason, ?string $comment = null): void
    {
        DB::transaction(function () use ($kyc, $reviewer, $reason, $comment) {
            // Update KYC status
            $this->requestRepository->update($kyc->id, [
                'status' => 'needs_correction',
            ]);

            // Record rejection reason (reuse for correction requests)
            $kyc->rejections()->create([
                'uuid' => Str::uuid()->toString(),
                'operator_id' => $reviewer->id,
                'reason' => "Correction requested: {$reason}",
                'comment' => $comment,
            ]);

            // Record status history
            $this->recordStatusHistory($kyc, 'submitted', 'needs_correction', $reviewer, 'reviewer', $comment ?? $reason);

            // Record audit trail
            $this->recordAuditTrail($kyc->user_id, 'kyc_correction_requested', 'KYC Correction Requested', [
                'reviewed_by' => $reviewer->id,
                'reason' => $reason,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Securely download a KYC document
     */
    public function downloadDocument(KycDocument $document, User $downloader): string
    {
        // Verify access
        $this->authorizeDocumentAccess($document, $downloader);

        // Record download
        $this->recordAuditTrail($downloader->id, 'kyc_document_downloaded', 'KYC Document Downloaded', [
            'document_id' => $document->id,
            'kyc_request_id' => $document->kyc_request_id,
        ]);

        // Return download URL (private disk)
        return $this->storageService->url($document->file_path);
    }

    /**
     * Get KYC workflow definition (or create if not exists)
     */
    protected function getOrCreateKycWorkflowDefinition(): WorkflowDefinition
    {
        $definition = WorkflowDefinition::where('key', 'kyc-verification-workflow')->first();

        if (!$definition) {
            $definition = WorkflowDefinition::create([
                'name' => 'KYC Verification Workflow',
                'key' => 'kyc-verification-workflow',
                'entity_type' => 'KYC',
                'version' => 1,
                'is_active' => true,
                'is_default' => true,
            ]);
        }

        return $definition;
    }

    /**
     * Get KYC approval definition (or create if not exists)
     */
    protected function getOrCreateKycApprovalDefinition(WorkflowDefinition $workflowDefinition): ApprovalDefinition
    {
        $definition = ApprovalDefinition::where('key', 'kyc-review-approval')->first();

        if (!$definition) {
            $definition = ApprovalDefinition::create([
                'workflow_definition_id' => $workflowDefinition->id,
                'name' => 'KYC Review and Approval',
                'key' => 'kyc-review-approval',
                'description' => 'Reviews and approves KYC verification applications',
                'approval_mode' => 'any',
                'required_approval_count' => 1,
                'is_active' => true,
            ]);
        }

        return $definition;
    }

    /**
     * Create identity snapshot for audit trail
     */
    protected function createIdentitySnapshot(KycRequest $kyc): void
    {
        $profile = $kyc->profile;

        if ($profile) {
            KycIdentitySnapshot::create([
                'uuid' => Str::uuid()->toString(),
                'kyc_request_id' => $kyc->id,
                'first_name' => $kyc->user->name ?? null,
                'father_name' => $profile->father_name,
                'national_code' => $profile->national_code,
                'birth_date' => $profile->birth_date,
                'postal_code' => $profile->postal_code,
                'address' => $profile->address,
                'metadata' => [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'submitted_at' => now()->toIso8601String(),
                ],
            ]);
        }
    }

    /**
     * Validate required documents are present
     */
    protected function validateRequiredDocuments(KycRequest $kyc): void
    {
        $requiredDocuments = config('kyc.required_documents', [
            'national_id_front',
            'national_id_back',
            'birth_certificate',
            'declaration',
            'handwritten_signature',
        ]);

        $uploadedDocuments = $kyc->documents()
            ->whereIn('document_type', $requiredDocuments)
            ->where('document_status', '!=', 'deleted')
            ->pluck('document_type')
            ->toArray();

        $missing = array_diff($requiredDocuments, $uploadedDocuments);

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required documents: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Validate document file
     */
    protected function validateDocumentFile(UploadedFile $file): void
    {
        $maxSize = config('kyc.max_document_size', 10 * 1024 * 1024); // 10MB default
        $allowedMimes = config('kyc.allowed_mime_types', [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ]);

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size.');
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('File type not allowed.');
        }

        // Check for dangerous files
        if ($this->isDangerousFile($file)) {
            throw new \InvalidArgumentException('File type is not allowed.');
        }
    }

    /**
     * Check if file is potentially dangerous
     */
    protected function isDangerousFile(UploadedFile $file): bool
    {
        $dangerousExtensions = ['exe', 'dll', 'bat', 'cmd', 'scr', 'vbs', 'js', 'jar', 'zip', 'rar'];
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, $dangerousExtensions);
    }

    /**
     * Authorize document access
     */
    protected function authorizeDocumentAccess(KycDocument $document, User $downloader): void
    {
        $kyc = $document->kycRequest;

        // Owner can always download their own documents
        if ($kyc->user_id === $downloader->id) {
            return;
        }

        // Check permissions
        if (!$downloader->hasPermissionTo('kyc.download_documents')) {
            throw new \RuntimeException('Unauthorized to download this document.');
        }

        // If reviewer role, ensure they have access to review KYC
        if ($downloader->hasPermissionTo('kyc.review')) {
            // Additional checks can be added for role-based access
            return;
        }

        throw new \RuntimeException('Unauthorized to download this document.');
    }

    /**
     * Record status history
     */
    protected function recordStatusHistory(
        KycRequest $kyc,
        ?string $oldStatus,
        ?string $newStatus,
        ?User $changedBy = null,
        ?string $role = null,
        ?string $comment = null
    ): void {
        $kyc->statusHistory()->create([
            'uuid' => Str::uuid()->toString(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy?->id,
            'role' => $role,
            'comment' => $comment,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Record audit trail
     */
    protected function recordAuditTrail(
        int $userId,
        string $action,
        string $actionLabel,
        array $metadata = []
    ): void {
        \App\Models\AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'action_label' => $actionLabel,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
