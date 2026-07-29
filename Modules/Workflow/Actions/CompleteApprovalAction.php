<?php

namespace Modules\Workflow\Actions;

use App\Models\User;
use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalInstance;

class CompleteApprovalAction
{
    public function __construct(protected ApprovalEngineInterface $engine)
    {
    }

    public function execute(ApprovalInstance $approvalInstance, User $user, array $payload = []): ApprovalDecision
    {
        return $this->engine->approve($approvalInstance, $user, $payload);
    }
}
