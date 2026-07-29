<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WorkflowIdempotencyKey
 * 
 * Prevents duplicate workflow transitions from being executed.
 * 
 * Example:
 * - Client sends transition request with idempotency_key="req-123"
 * - First attempt: Executes and saves to database
 * - Second attempt (duplicate): Finds existing entry, returns same result
 * 
 * This ensures that network retries or client errors don't
 * cause duplicate state transitions.
 */
class WorkflowIdempotencyKey extends Model
{
    use HasFactory;

    protected $table = 'workflow_idempotency_keys';

    protected $fillable = [
        'key',
        'workflow_instance_id',
        'transition_id',
        'request_hash',
        'executed_by',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    /**
     * Get the workflow instance
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    /**
     * Get the transition
     */
    public function transition(): BelongsTo
    {
        return $this->belongsTo(WorkflowTransition::class, 'transition_id');
    }

    /**
     * Get the user who executed this
     */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'executed_by');
    }

    /**
     * Check if this idempotency key has been used
     */
    public static function exists(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Get an existing idempotency key record
     */
    public static function findByKey(string $key): ?static
    {
        return static::where('key', $key)->first();
    }
}
