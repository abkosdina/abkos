<?php

namespace Modules\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;

class ApprovalStepStarted
{
    use Dispatchable;

    public function __construct(
        public ApprovalInstance $approvalInstance,
        public ApprovalInstanceStep $approvalInstanceStep,
    ) {
    }
}
