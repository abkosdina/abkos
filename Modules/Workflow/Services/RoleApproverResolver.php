<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Interfaces\ApproverResolverInterface;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Spatie\Permission\Models\Role;

class RoleApproverResolver implements ApproverResolverInterface
{
    public function resolve(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection
    {
        $approvalStep = $step->approvalStep;
        if (! $approvalStep || ! $approvalStep->required_role) {
            return new Collection();
        }

        if (! Role::query()->where('name', $approvalStep->required_role)->exists()) {
            return new Collection();
        }

        return User::query()
            ->role($approvalStep->required_role)
            ->get();
    }

    public function supports(ApprovalStep $step): bool
    {
        return (bool) $step->required_role;
    }
}
