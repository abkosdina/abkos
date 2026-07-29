<?php

namespace App\Repositories\Contracts;

/**
 * WorkflowIdempotencyRepository
 * 
 * Data access layer for WorkflowIdempotencyKey model
 */
interface WorkflowIdempotencyRepository
{
    /**
     * Get an idempotency key by key string
     */
    public function findByKey(string $key): ?\App\Models\WorkflowIdempotencyKey;

    /**
     * Check if an idempotency key exists
     */
    public function exists(string $key): bool;

    /**
     * Create a new idempotency key record
     */
    public function create(array $data): \App\Models\WorkflowIdempotencyKey;

    /**
     * Get all idempotency keys for an instance
     */
    public function findByInstance(int $instanceId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Clean up old idempotency keys (older than N days)
     */
    public function cleanupOld(int $days = 30): int;
}
