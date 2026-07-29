<?php

namespace Modules\Workflow\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;

class ApprovalCompleted
{
    use Dispatchable;

    public function __construct(
        public ApprovalInstance $approvalInstance,
        public ApprovalInstanceStep $approvalInstanceStep,
        public ApprovalDecision $approvalDecision,
        public User $actor,
    ) {
    }
}
