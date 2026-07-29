<?php

namespace Modules\Workflow\Repositories\Eloquent;

use Modules\Workflow\Models\WorkflowStep;
use Modules\Workflow\Repositories\Interfaces\WorkflowStepRepositoryInterface;

class WorkflowStepRepository implements WorkflowStepRepositoryInterface
{
    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return WorkflowStep::query()->find($id, $columns);
    }

    public function getByDefinition(int|string $workflowDefinitionId): array
    {
        return WorkflowStep::query()->where('workflow_definition_id', $workflowDefinitionId)->orderBy('step_number')->get()->all();
    }

    public function create(array $data): object
    {
        return WorkflowStep::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return WorkflowStep::query()->findOrFail($id)->update($data);
    }
}
