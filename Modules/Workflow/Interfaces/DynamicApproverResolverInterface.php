<?php

namespace Modules\Workflow\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;

interface DynamicApproverResolverInterface
{
    public function resolve(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection;

    public function supports(array $configuration = []): bool;

    public function getKey(): string;

    public function getLabel(): string;
}
