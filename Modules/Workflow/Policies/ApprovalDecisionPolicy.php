<?php

namespace Modules\Workflow\Policies;

use App\Models\User;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Services\ApprovalAuthorizationService;

class ApprovalDecisionPolicy
{
    public function __construct(protected ApprovalAuthorizationService $authorizationService)
    {
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ApprovalDecision $approvalDecision): bool
    {
        return true;
    }
}
