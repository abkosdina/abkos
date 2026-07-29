<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Modules\Workflow\Interfaces\SelfApprovalRuleInterface;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;

class DefaultSelfApprovalRule implements SelfApprovalRuleInterface
{
    public function shouldBlock(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): bool
    {
        $creatorId = $approvalInstance->workflowInstance?->metadata['creator_user_id'] ?? null;
        return $creatorId && (int) $creatorId === (int) $user->id;
    }

    public function getReason(): string
    {
        return 'The creator of the business process cannot approve their own request.';
    }
}
