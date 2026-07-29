<?php

namespace App\Repositories\Contracts;

/**
 * WorkflowStepRepository
 * 
 * Data access layer for WorkflowInstanceStep model
 */
interface WorkflowStepRepository
{
    /**
     * Get a step by ID
     */
    public function findById(int $id): ?\App\Models\WorkflowInstanceStep;

    /**
     * Get all steps for a workflow instance
     */
    public function findByInstance(int $instanceId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get the last step in a workflow instance
     */
    public function getLastStep(int $instanceId): ?\App\Models\WorkflowInstanceStep;

    /**
     * Create a new step
     */
    public function create(array $data): \App\Models\WorkflowInstanceStep;

    /**
     * Get steps by idempotency key
     */
    public function findByIdempotencyKey(string $key): ?\App\Models\WorkflowInstanceStep;

    /**
     * Get steps executed by a specific user
     */
    public function findByExecutor(int $userId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get steps within a date range
     */
    public function findByDateRange(\DateTime $from, \DateTime $to): \Illuminate\Database\Eloquent\Collection;
}
