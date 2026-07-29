<?php

namespace Modules\Workflow\Policies;

use App\Models\User;
use Modules\Workflow\Models\ApprovalDelegation;
use Modules\Workflow\Services\ApprovalAuthorizationService;

class ApprovalDelegationPolicy
{
    public function __construct(protected ApprovalAuthorizationService $authorizationService)
    {
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ApprovalDelegation $approvalDelegation): bool
    {
        return true;
    }
}
