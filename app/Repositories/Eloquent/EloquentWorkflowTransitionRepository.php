<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowTransition;
use App\Repositories\Contracts\WorkflowTransitionRepository;

class EloquentWorkflowTransitionRepository implements WorkflowTransitionRepository
{
    public function findById(int $id): ?WorkflowTransition
    {
        return WorkflowTransition::find($id);
    }

    public function findByKey(int $definitionId, string $key): ?WorkflowTransition
    {
        return WorkflowTransition::where('workflow_definition_id', $definitionId)
            ->where('key', $key)
            ->first();
    }

    public function findFromState(int $fromStateId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowTransition::where('from_state_id', $fromStateId)
            ->where('is_active', true)
            ->get();
    }

    public function findTransition(int $fromStateId, int $toStateId): ?WorkflowTransition
    {
        return WorkflowTransition::where('from_state_id', $fromStateId)
            ->where('to_state_id', $toStateId)
            ->where('is_active', true)
            ->first();
    }

    public function findByDefinition(int $definitionId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowTransition::where('workflow_definition_id', $definitionId)->get();
    }

    public function getActiveTransitions(int $definitionId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowTransition::where('workflow_definition_id', $definitionId)
            ->where('is_active', true)
            ->get();
    }

    public function create(array $data): WorkflowTransition
    {
        return WorkflowTransition::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $transition = $this->findById($id);
        if (!$transition) {
            return false;
        }
        return $transition->update($data);
    }

    public function exists(int $fromStateId, int $toStateId): bool
    {
        return WorkflowTransition::where('from_state_id', $fromStateId)
            ->where('to_state_id', $toStateId)
            ->where('is_active', true)
            ->exists();
    }
}
