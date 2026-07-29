<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowAssignment;
use Modules\Workflow\Repositories\Interfaces\WorkflowAssignmentRepositoryInterface;

class WorkflowAssignmentRepository implements WorkflowAssignmentRepositoryInterface
{
    public function create(array $data): object
    {
        return WorkflowAssignment::query()->create($data);
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowAssignment::query()->find($id, $columns);
    }

    public function getByStep(int|string $stepId): array
    {
        return WorkflowAssignment::query()->where('workflow_step_id', $stepId)->orderBy('priority')->get()->all();
    }
}
