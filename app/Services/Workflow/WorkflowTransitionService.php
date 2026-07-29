<?php

namespace App\Services\Workflow;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowTransition;
use App\Repositories\Contracts\WorkflowTransitionRepository;
use Illuminate\Database\Eloquent\Collection;

class WorkflowTransitionService
{
    public function __construct(protected WorkflowTransitionRepository $repository)
    {
    }

    public function getByDefinition(WorkflowDefinition $definition): Collection
    {
        return $this->repository->findByDefinition($definition->id);
    }

    public function findByKey(int $definitionId, string $key): ?WorkflowTransition
    {
        return $this->repository->findByKey($definitionId, $key);
    }

    public function findFromState(int $stateId): Collection
    {
        return $this->repository->findFromState($stateId);
    }
}
