<?php

namespace Modules\Workflow\Actions;

use App\Models\User;
use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalInstance;

class RejectAction
{
    public function __construct(protected ApprovalEngineInterface $engine)
    {
    }

    public function execute(ApprovalInstance $approvalInstance, User $user, string $reason, ?string $comment = null, array $payload = []): ApprovalDecision
    {
        return $this->engine->reject($approvalInstance, $user, $reason, $comment, $payload);
    }
}
