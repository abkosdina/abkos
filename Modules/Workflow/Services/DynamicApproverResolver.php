<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Interfaces\ApproverResolverInterface;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;

class DynamicApproverResolver implements ApproverResolverInterface
{
    public function resolve(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection
    {
        $configuration = $step->approvalStep?->configuration ?? [];
        if (empty($configuration['dynamic_key'])) {
            return new Collection();
        }

        $key = $configuration['dynamic_key'];
        if ($key === 'owner-manager') {
            $creatorId = $approvalInstance->workflowInstance?->metadata['creator_user_id'] ?? null;
            if (! $creatorId) {
                return new Collection();
            }

            $manager = User::query()->find($creatorId);
            return $manager ? new Collection([$manager]) : new Collection();
        }

        return new Collection();
    }

    public function supports(ApprovalStep $step): bool
    {
        $configuration = $step->configuration ?? [];

        return ! empty($configuration['dynamic_key']);
    }

    public function getKey(): string
    {
        return 'dynamic';
    }

    public function getLabel(): string
    {
        return 'Dynamic approver';
    }
}
