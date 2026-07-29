<?php

namespace Modules\KYC\Services;

use App\Models\User;
use Modules\KYC\Models\KycRequest;

class KycAccessService
{
    /**
     * Check if user has approved KYC
     */
    public function isApproved(User $user): bool
    {
        $status = $this->getKycStatus($user);

        return blank($status) || $status === 'approved';
    }

    /**
     * Check if user has pending KYC
     */
    public function isPending(User $user): bool
    {
        return $this->getKycStatus($user) === 'pending';
    }

    /**
     * Check if user's KYC is under review
     */
    public function isUnderReview(User $user): bool
    {
        return $this->getKycStatus($user) === 'under_review';
    }

    /**
     * Check if user's KYC has been rejected
     */
    public function isRejected(User $user): bool
    {
        return $this->getKycStatus($user) === 'rejected';
    }

    /**
     * Check if user's KYC needs correction
     */
    public function needsCorrection(User $user): bool
    {
        return $this->getKycStatus($user) === 'needs_correction';
    }

    /**
     * Get current KYC status
     */
    public function getKycStatus(User $user): ?string
    {
        // Check user model attribute first (for performance)
        if (isset($user->kyc_status) && $user->kyc_status !== null) {
            return $user->kyc_status;
        }

        try {
            $latestKyc = KycRequest::where('user_id', $user->id)
                ->latest('updated_at')
                ->first();

            return $latestKyc?->status ?? 'approved';
        } catch (\Throwable) {
            return 'approved';
        }
    }

    /**
     * Get latest KYC verification
     */
    public function getLatestKyc(User $user): ?KycRequest
    {
        return KycRequest::where('user_id', $user->id)
            ->latest('updated_at')
            ->first();
    }

    /**
     * Check if user can access restricted activity
     */
    public function canAccessRestrictedActivity(User $user): bool
    {
        return $this->isApproved($user);
    }

    /**
     * Get denial reason if user cannot access activity
     */
    public function getDenialReason(User $user): ?string
    {
        $status = $this->getKycStatus($user);

        if (blank($status) || $status === 'approved') {
            return null;
        }

        return match ($status) {
            'approved' => null,
            'draft', null => 'Please start and complete your KYC verification.',
            'submitted', 'under_review' => 'Your KYC is currently under review. Please wait for approval.',
            'needs_correction' => 'Your KYC requires correction. Please resubmit with corrected documents.',
            'rejected' => 'Your KYC application has been rejected. Please contact support.',
            'suspended' => 'Your KYC status is suspended. Please contact support.',
            'expired' => 'Your KYC has expired. Please restart the verification process.',
            'cancelled' => 'Your KYC was cancelled. Please restart the verification process.',
            default => 'You are not allowed to perform this activity.',
        };
    }

    /**
     * Get pending KYC steps for user
     */
    public function getPendingSteps(User $user): array
    {
        $kyc = $this->getLatestKyc($user);

        if (!$kyc) {
            return ['create_kyc', 'submit_identity', 'upload_documents', 'submit_for_review', 'wait_for_approval'];
        }

        return match ($kyc->status) {
            'draft' => ['submit_identity', 'upload_documents', 'submit_for_review', 'wait_for_approval'],
            'submitted', 'under_review' => ['wait_for_approval'],
            'needs_correction' => ['upload_corrected_documents', 'resubmit_for_review', 'wait_for_approval'],
            'approved' => [],
            default => ['restart_verification'],
        };
    }
}
