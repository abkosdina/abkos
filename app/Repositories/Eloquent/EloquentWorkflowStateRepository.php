<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowState;
use App\Repositories\Contracts\WorkflowStateRepository;

class EloquentWorkflowStateRepository implements WorkflowStateRepository
{
    public function findById(int $id): ?WorkflowState
    {
        return WorkflowState::find($id);
    }

    public function findByKey(int $definitionId, string $key): ?WorkflowState
    {
        return WorkflowState::where('workflow_definition_id', $definitionId)
            ->where('key', $key)
            ->first();
    }

    public function getInitialState(int $definitionId): ?WorkflowState
    {
        return WorkflowState::where('workflow_definition_id', $definitionId)
            ->where('is_initial', true)
            ->first();
    }

    public function findByDefinition(int $definitionId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowState::where('workflow_definition_id', $definitionId)
            ->orderBy('sort_order')
            ->get();
    }

    public function getActiveStates(int $definitionId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowState::where('workflow_definition_id', $definitionId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function create(array $data): WorkflowState
    {
        return WorkflowState::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $state = $this->findById($id);
        if (!$state) {
            return false;
        }
        return $state->update($data);
    }

    public function getFinalStates(int $definitionId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowState::where('workflow_definition_id', $definitionId)
            ->where('is_final', true)
            ->get();
    }

    public function getRejectionStates(int $definitionId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowState::where('workflow_definition_id', $definitionId)
            ->where('is_rejection', true)
            ->get();
    }
}
