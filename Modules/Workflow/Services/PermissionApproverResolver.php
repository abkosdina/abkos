<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Interfaces\ApproverResolverInterface;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Spatie\Permission\Models\Permission;

class PermissionApproverResolver implements ApproverResolverInterface
{
    public function resolve(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection
    {
        $approvalStep = $step->approvalStep;
        if (! $approvalStep || ! $approvalStep->required_permission) {
            return new Collection();
        }

        if (! Permission::query()->where('name', $approvalStep->required_permission)->exists()) {
            return new Collection();
        }

        return User::query()
            ->permission($approvalStep->required_permission)
            ->get();
    }

    public function supports(ApprovalStep $step): bool
    {
        return (bool) $step->required_permission;
    }
}
