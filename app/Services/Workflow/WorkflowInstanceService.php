<?php

namespace App\Services\Workflow;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Repositories\Contracts\WorkflowInstanceRepository;
use Illuminate\Database\Eloquent\Collection;

class WorkflowInstanceService
{
    public function __construct(protected WorkflowInstanceRepository $repository)
    {
    }

    public function findById(int $id): ?WorkflowInstance
    {
        return $this->repository->findById($id);
    }

    public function findForEntity(string $entityType, int $entityId, ?int $definitionId = null): ?WorkflowInstance
    {
        return $this->repository->findForEntity($entityType, $entityId, $definitionId);
    }

    public function getActive(?WorkflowDefinition $definition = null): Collection
    {
        return $this->repository->getActive($definition);
    }

    public function getCompleted(?WorkflowDefinition $definition = null): Collection
    {
        return $this->repository->getCompleted($definition);
    }
}
