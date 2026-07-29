<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowInstance;
use App\Models\WorkflowDefinition;
use App\Repositories\Contracts\WorkflowInstanceRepository;

class EloquentWorkflowInstanceRepository implements WorkflowInstanceRepository
{
    public function findById(int $id): ?WorkflowInstance
    {
        return WorkflowInstance::find($id);
    }

    public function findByUuid(string $uuid): ?WorkflowInstance
    {
        return WorkflowInstance::where('uuid', $uuid)->first();
    }

    public function findForEntity(string $entityType, int $entityId, ?int $definitionId = null): ?WorkflowInstance
    {
        $query = WorkflowInstance::where('entity_type', $entityType)
            ->where('entity_id', $entityId);

        if ($definitionId) {
            $query->where('workflow_definition_id', $definitionId);
        }

        return $query->first();
    }

    public function findAllForEntity(string $entityType, int $entityId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowInstance::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();
    }

    public function create(array $data): WorkflowInstance
    {
        return WorkflowInstance::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $instance = $this->findById($id);
        if (!$instance) {
            return false;
        }
        return $instance->update($data);
    }

    public function getActive(?WorkflowDefinition $definition = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = WorkflowInstance::where('status', 'active');

        if ($definition) {
            $query->where('workflow_definition_id', $definition->id);
        }

        return $query->get();
    }

    public function getCompleted(?WorkflowDefinition $definition = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = WorkflowInstance::where('status', 'completed');

        if ($definition) {
            $query->where('workflow_definition_id', $definition->id);
        }

        return $query->get();
    }

    public function lockForUpdate(WorkflowInstance $instance): WorkflowInstance
    {
        // Reload the instance with a lock
        return WorkflowInstance::where('id', $instance->id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
