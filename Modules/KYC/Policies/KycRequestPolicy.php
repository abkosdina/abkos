<?php

namespace Modules\KYC\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\KYC\Models\KycRequest;

class KycRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, KycRequest $kycRequest): bool
    {
        if ($user->hasRole(['Admin', 'Super Admin', 'Operator', 'Senior Operator'])) {
            return true;
        }

        return $kycRequest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, KycRequest $kycRequest): bool
    {
        return $kycRequest->user_id === $user->id
            && in_array($kycRequest->status, ['draft', 'needs_correction', 'rejected'], true);
    }

    public function submit(User $user, KycRequest $kycRequest): bool
    {
        return $kycRequest->user_id === $user->id
            && in_array($kycRequest->status, ['draft', 'needs_correction', 'rejected'], true);
    }

    public function uploadDocument(User $user, KycRequest $kycRequest): bool
    {
        return $kycRequest->user_id === $user->id
            && in_array($kycRequest->status, ['draft', 'needs_correction', 'rejected'], true);
    }

    public function approve(User $user, KycRequest $kycRequest): bool
    {
        return $user->hasAnyRole(['Operator', 'Senior Operator', 'Admin', 'Super Admin'])
            || $user->hasPermissionTo('kyc.approve');
    }

    public function reject(User $user, KycRequest $kycRequest): bool
    {
        return $this->approve($user, $kycRequest);
    }

    public function requestCorrection(User $user, KycRequest $kycRequest): bool
    {
        return $this->approve($user, $kycRequest);
    }

    public function downloadDocument(User $user, KycRequest $kycRequest): bool
    {
        if ($kycRequest->user_id === $user->id) {
            return true;
        }

        return $user->hasPermissionTo('kyc.download_documents')
            || $user->hasAnyRole(['Operator', 'Senior Operator', 'Admin', 'Super Admin']);
    }
}
