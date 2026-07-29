<?php

namespace Modules\Workflow\Policies;

use App\Models\User;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Services\ApprovalAuthorizationService;

class ApprovalInstanceStepPolicy
{
    public function __construct(protected ApprovalAuthorizationService $authorizationService)
    {
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ApprovalInstanceStep $approvalInstanceStep): bool
    {
        return true;
    }
}
