<?php

namespace App\Repositories\Contracts;

/**
 * WorkflowStateRepository
 * 
 * Data access layer for WorkflowState model
 */
interface WorkflowStateRepository
{
    /**
     * Get a state by ID
     */
    public function findById(int $id): ?\App\Models\WorkflowState;

    /**
     * Get a state by key within a definition
     */
    public function findByKey(int $definitionId, string $key): ?\App\Models\WorkflowState;

    /**
     * Get initial state of a workflow
     */
    public function getInitialState(int $definitionId): ?\App\Models\WorkflowState;

    /**
     * Get all states for a definition
     */
    public function findByDefinition(int $definitionId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get all active states for a definition
     */
    public function getActiveStates(int $definitionId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Create a new state
     */
    public function create(array $data): \App\Models\WorkflowState;

    /**
     * Update a state
     */
    public function update(int $id, array $data): bool;

    /**
     * Get final states for a definition
     */
    public function getFinalStates(int $definitionId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get rejection states for a definition
     */
    public function getRejectionStates(int $definitionId): \Illuminate\Database\Eloquent\Collection;
}
