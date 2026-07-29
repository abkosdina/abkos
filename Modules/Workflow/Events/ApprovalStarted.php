<?php

namespace Modules\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Workflow\Models\ApprovalInstance;

class ApprovalStarted
{
    use Dispatchable;

    public function __construct(public ApprovalInstance $approvalInstance)
    {
    }
}
