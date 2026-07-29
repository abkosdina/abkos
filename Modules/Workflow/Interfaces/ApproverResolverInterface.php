<?php

namespace Modules\Workflow\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;

interface ApproverResolverInterface
{
    public function resolve(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection;

    public function supports(ApprovalStep $step): bool;
}
