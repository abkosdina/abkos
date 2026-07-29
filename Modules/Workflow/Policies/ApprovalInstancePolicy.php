<?php

namespace Modules\Workflow\Policies;

use App\Models\User;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Services\ApprovalAuthorizationService;

class ApprovalInstancePolicy
{
    public function __construct(protected ApprovalAuthorizationService $authorizationService)
    {
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ApprovalInstance $approvalInstance): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ApprovalInstance $approvalInstance): bool
    {
        return true;
    }

    public function delete(User $user, ApprovalInstance $approvalInstance): bool
    {
        return true;
    }
}
