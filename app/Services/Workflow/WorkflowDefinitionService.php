<?php

namespace App\Services\Workflow;

use App\Models\WorkflowDefinition;
use App\Repositories\Contracts\WorkflowDefinitionRepository;
use Illuminate\Database\Eloquent\Collection;

class WorkflowDefinitionService
{
    public function __construct(protected WorkflowDefinitionRepository $repository)
    {
    }

    public function getByKey(string $key, ?int $version = null): ?WorkflowDefinition
    {
        return $this->repository->findByKey($key, $version);
    }

    public function getById(int $id): ?WorkflowDefinition
    {
        return $this->repository->findById($id);
    }

    public function getByEntityType(string $entityType): Collection
    {
        return $this->repository->findByEntityType($entityType);
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function create(array $data): WorkflowDefinition
    {
        return $this->repository->create($data);
    }

    public function getDefaultForEntity(string $entityType): ?WorkflowDefinition
    {
        return $this->repository->findByEntityType($entityType)
            ->first(fn (WorkflowDefinition $definition) => $definition->is_default || $definition->is_active);
    }
}
