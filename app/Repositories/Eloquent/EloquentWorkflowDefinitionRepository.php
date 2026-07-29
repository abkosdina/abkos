<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowDefinition;
use App\Repositories\Contracts\WorkflowDefinitionRepository;

class EloquentWorkflowDefinitionRepository implements WorkflowDefinitionRepository
{
    public function findByKey(string $key, ?int $version = null): ?WorkflowDefinition
    {
        $query = WorkflowDefinition::where('key', $key);

        if ($version) {
            $query->where('version', $version);
        }

        return $query->first();
    }

    public function findById(int $id): ?WorkflowDefinition
    {
        return WorkflowDefinition::find($id);
    }

    public function findByEntityType(string $entityType): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowDefinition::where('entity_type', $entityType)
            ->where('is_active', true)
            ->get();
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowDefinition::where('is_active', true)->get();
    }

    public function create(array $data): WorkflowDefinition
    {
        return WorkflowDefinition::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $definition = $this->findById($id);
        if (!$definition) {
            return false;
        }
        return $definition->update($data);
    }

    public function getLatestVersion(string $key): ?WorkflowDefinition
    {
        return WorkflowDefinition::where('key', $key)
            ->orderBy('version', 'desc')
            ->first();
    }
}
