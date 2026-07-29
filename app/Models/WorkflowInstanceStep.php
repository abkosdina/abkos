<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * WorkflowInstanceStep
 * 
 * Records a single step (transition) in a workflow instance.
 * This is the audit trail / history of the workflow process.
 * 
 * Example:
 * - Advertisement #100, Step 1: Draft → PendingReview (by User #1)
 * - Advertisement #100, Step 2: PendingReview → Approved (by Operator #5)
 * - Advertisement #100, Step 3: Approved → Published (by Operator #5)
 */
class WorkflowInstanceStep extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'workflow_instance_steps';

    protected $fillable = [
        'uuid',
        'workflow_instance_id',
        'transition_id',
        'from_state_id',
        'to_state_id',
        'executed_by',
        'idempotency_key',
        'comment',
        'reason',
        'metadata',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the workflow instance
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    /**
     * Get the transition that was executed
     */
    public function transition(): BelongsTo
    {
        return $this->belongsTo(WorkflowTransition::class, 'transition_id');
    }

    /**
     * Get the "from" state
     */
    public function fromState(): BelongsTo
    {
        return $this->belongsTo(WorkflowState::class, 'from_state_id');
    }

    /**
     * Get the "to" state
     */
    public function toState(): BelongsTo
    {
        return $this->belongsTo(WorkflowState::class, 'to_state_id');
    }

    /**
     * Get the user who executed this step
     */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'executed_by');
    }
}
