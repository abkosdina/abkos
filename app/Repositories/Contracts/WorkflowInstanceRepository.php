<?php

namespace App\Repositories\Contracts;

/**
 * WorkflowInstanceRepository
 * 
 * Data access layer for WorkflowInstance model
 */
interface WorkflowInstanceRepository
{
    /**
     * Get a workflow instance by ID
     */
    public function findById(int $id): ?\App\Models\WorkflowInstance;

    /**
     * Get a workflow instance by UUID
     */
    public function findByUuid(string $uuid): ?\App\Models\WorkflowInstance;

    /**
     * Get workflow instance for a specific entity
     */
    public function findForEntity(string $entityType, int $entityId, ?int $definitionId = null): ?\App\Models\WorkflowInstance;

    /**
     * Get all instances for an entity (may have multiple workflows)
     */
    public function findAllForEntity(string $entityType, int $entityId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Create a new workflow instance
     */
    public function create(array $data): \App\Models\WorkflowInstance;

    /**
     * Update a workflow instance
     */
    public function update(int $id, array $data): bool;

    /**
     * Get active workflow instances
     */
    public function getActive(?\App\Models\WorkflowDefinition $definition = null): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get completed workflow instances
     */
    public function getCompleted(?\App\Models\WorkflowDefinition $definition = null): \Illuminate\Database\Eloquent\Collection;

    /**
     * Lock a workflow instance for update (prevents concurrent access)
     */
    public function lockForUpdate(\App\Models\WorkflowInstance $instance): \App\Models\WorkflowInstance;
}
