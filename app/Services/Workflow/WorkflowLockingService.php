<?php

namespace App\Services\Workflow;

use App\Models\WorkflowInstance;

/**
 * WorkflowLockingService
 * 
 * Handles database-level locking to prevent concurrent transitions.
 * Uses pessimistic locking (SELECT FOR UPDATE) to ensure only one
 * request can update a workflow instance at a time.
 */
class WorkflowLockingService
{
    /**
     * Acquire a lock on a workflow instance
     * 
     * This uses database row-level locking to prevent concurrent access.
     * Only one request can hold the lock at a time.
     */
    public function lock(WorkflowInstance $instance, int $timeoutSeconds = 30): WorkflowInstance
    {
        // Reload with exclusive lock
        return WorkflowInstance::where('id', $instance->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Release lock (automatic when transaction commits)
     */
    public function release(WorkflowInstance $instance): void
    {
        // Locks are automatically released when the transaction commits
        // This method is here for clarity/documentation
    }

    /**
     * Check version before updating (optimistic locking alternative)
     */
    public function validateVersion(WorkflowInstance $instance, int $expectedVersion): bool
    {
        return $instance->version === $expectedVersion;
    }

    /**
     * Get fresh instance with lock
     */
    public function getFresh(int $id): ?WorkflowInstance
    {
        return WorkflowInstance::where('id', $id)
            ->lockForUpdate()
            ->first();
    }
}
