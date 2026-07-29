<?php

namespace App\Services\Workflow;

use App\Models\WorkflowInstance;
use App\Models\WorkflowIdempotencyKey;
use App\Models\WorkflowTransition;
use App\Repositories\Contracts\WorkflowIdempotencyRepository;

/**
 * WorkflowIdempotencyService
 * 
 * Prevents duplicate workflow transitions from being executed.
 * 
 * Example:
 * - Client sends transition request with idempotency_key="req-12345"
 * - Server processes and saves idempotency key
 * - Client retries with same key (network error, timeout, etc.)
 * - Server returns cached result without re-executing transition
 * 
 * This is crucial for distributed systems where requests can be retried.
 */
class WorkflowIdempotencyService
{
    protected WorkflowIdempotencyRepository $idempotencyRepository;

    public function __construct(WorkflowIdempotencyRepository $idempotencyRepository)
    {
        $this->idempotencyRepository = $idempotencyRepository;
    }

    /**
     * Check if a request with this idempotency key has already been processed
     */
    public function isDuplicate(string $key, int $instanceId): bool
    {
        $existing = $this->idempotencyRepository->findByKey($key);
        
        if (!$existing) {
            return false;
        }

        // If the existing key is for a different instance, it might be a collision
        if ($existing->workflow_instance_id !== $instanceId) {
            throw new \Exception("Idempotency key collision detected");
        }

        return true;
    }

    /**
     * Record that a transition was executed with this idempotency key
     */
    public function record(
        string $key,
        WorkflowInstance $instance,
        WorkflowTransition $transition
    ): WorkflowIdempotencyKey {
        return $this->idempotencyRepository->create([
            'key' => $key,
            'workflow_instance_id' => $instance->id,
            'transition_id' => $transition->id,
            'request_hash' => hash('sha256', serialize($transition->toArray())),
            'executed_by' => auth()->id(),
            'executed_at' => now(),
        ]);
    }

    /**
     * Get a previously recorded execution
     */
    public function getRecording(string $key): ?WorkflowIdempotencyKey
    {
        return $this->idempotencyRepository->findByKey($key);
    }

    /**
     * Generate an idempotency key from request data
     */
    public static function generateKey(array $data): string
    {
        return hash('sha256', serialize($data));
    }

    /**
     * Cleanup old idempotency keys (older than N days)
     */
    public function cleanup(int $days = 30): int
    {
        return $this->idempotencyRepository->cleanupOld($days);
    }
}
