<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Interfaces\ApproverResolverInterface;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;

class UserApproverResolver implements ApproverResolverInterface
{
    public function resolve(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection
    {
        $approvalStep = $step->approvalStep;
        if (! $approvalStep || ! $approvalStep->required_user_id) {
            return new Collection();
        }

        $user = User::query()->find($approvalStep->required_user_id);
        if (! $user) {
            return new Collection();
        }

        return new Collection([$user]);
    }

    public function supports(ApprovalStep $step): bool
    {
        return (bool) $step->required_user_id;
    }
}
