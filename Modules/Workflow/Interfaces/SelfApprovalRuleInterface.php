<?php

namespace Modules\Workflow\Interfaces;

use App\Models\User;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;

interface SelfApprovalRuleInterface
{
    public function shouldBlock(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): bool;

    public function getReason(): string;
}
