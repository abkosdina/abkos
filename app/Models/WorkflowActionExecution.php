<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkflowActionExecution extends Model
{
    use HasFactory;

    protected $table = 'workflow_action_executions';

    protected $fillable = [
        'uuid',
        'workflow_action_id',
        'workflow_definition_id',
        'workflow_transition_id',
        'workflow_instance_id',
        'approval_instance_id',
        'actor_id',
        'business_entity_type',
        'business_entity_id',
        'action_key',
        'action_version',
        'handler',
        'status',
        'idempotency_key',
        'attempts',
        'max_attempts',
        'retry_count',
        'backoff_seconds',
        'next_retry_at',
        'error_code',
        'error_message',
        'result',
        'metadata',
        'started_at',
        'completed_at',
        'failed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'result' => 'array',
        'metadata' => 'array',
        'next_retry_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'retry_count' => 'integer',
        'backoff_seconds' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
            $model->status = $model->status ?: 'pending';
            $model->attempts = $model->attempts ?? 0;
            $model->retry_count = $model->retry_count ?? 0;
            $model->max_attempts = $model->max_attempts ?? 3;
        });
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(WorkflowAction::class, 'workflow_action_id');
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }
}
