<?php

namespace App\Repositories\Contracts;

/**
 * WorkflowTransitionRepository
 * 
 * Data access layer for WorkflowTransition model
 */
interface WorkflowTransitionRepository
{
    /**
     * Get a transition by ID
     */
    public function findById(int $id): ?\App\Models\WorkflowTransition;

    /**
     * Get a transition by key within a definition
     */
    public function findByKey(int $definitionId, string $key): ?\App\Models\WorkflowTransition;

    /**
     * Get transitions from a specific state
     */
    public function findFromState(int $fromStateId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get a specific transition from one state to another
     */
    public function findTransition(int $fromStateId, int $toStateId): ?\App\Models\WorkflowTransition;

    /**
     * Get all transitions for a definition
     */
    public function findByDefinition(int $definitionId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get active transitions for a definition
     */
    public function getActiveTransitions(int $definitionId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Create a new transition
     */
    public function create(array $data): \App\Models\WorkflowTransition;

    /**
     * Update a transition
     */
    public function update(int $id, array $data): bool;

    /**
     * Check if a transition exists
     */
    public function exists(int $fromStateId, int $toStateId): bool;
}
