<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Repositories\Interfaces\WorkflowApprovalRepositoryInterface;

class WorkflowApprovalRepository implements WorkflowApprovalRepositoryInterface
{
    public function create(array $data): object
    {
        return WorkflowApproval::query()->create($data);
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowApproval::query()->find($id, $columns);
    }

    public function findByInstanceAndStep(int|string $instanceId, int|string $stepId, int|string $approverId): ?object
    {
        return WorkflowApproval::query()
            ->where('workflow_instance_id', $instanceId)
            ->where('step_id', $stepId)
            ->where('approver_id', $approverId)
            ->first();
    }

    public function getByInstance(int|string $instanceId): array
    {
        return WorkflowApproval::query()->where('workflow_instance_id', $instanceId)->get()->all();
    }

    public function update(int|string $id, array $data): bool
    {
        return WorkflowApproval::query()->findOrFail($id)->update($data);
    }
}
