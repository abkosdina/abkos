<?php

namespace App\Repositories\Contracts;

/**
 * WorkflowDefinitionRepository
 * 
 * Data access layer for WorkflowDefinition model
 */
interface WorkflowDefinitionRepository
{
    /**
     * Get a workflow definition by key
     */
    public function findByKey(string $key, ?int $version = null): ?\App\Models\WorkflowDefinition;

    /**
     * Get a workflow definition by ID
     */
    public function findById(int $id): ?\App\Models\WorkflowDefinition;

    /**
     * Get workflow definitions for an entity type
     */
    public function findByEntityType(string $entityType): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get active workflow definitions
     */
    public function getActive(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Create a new workflow definition
     */
    public function create(array $data): \App\Models\WorkflowDefinition;

    /**
     * Update a workflow definition
     */
    public function update(int $id, array $data): bool;

    /**
     * Get the latest version of a workflow
     */
    public function getLatestVersion(string $key): ?\App\Models\WorkflowDefinition;
}
