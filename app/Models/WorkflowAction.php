<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkflowAction extends Model
{
    use HasFactory;

    protected $table = 'workflow_actions';

    protected $fillable = [
        'uuid',
        'workflow_definition_id',
        'workflow_transition_id',
        'step_id',
        'event_name',
        'action_type',
        'handler',
        'key',
        'name',
        'description',
        'configuration',
        'payload',
        'is_active',
        'version',
        'blocking',
        'priority',
        'execution_order',
        'failure_policy',
        'max_attempts',
        'backoff_seconds',
        'executed_at',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'configuration' => 'array',
        'payload' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'blocking' => 'boolean',
        'priority' => 'integer',
        'execution_order' => 'integer',
        'version' => 'integer',
        'max_attempts' => 'integer',
        'backoff_seconds' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
            $model->handler = $model->handler ?: $model->action_type;
            $model->blocking = $model->blocking ?? true;
            $model->priority = $model->priority ?? 100;
            $model->execution_order = $model->execution_order ?? 1;
            $model->failure_policy = $model->failure_policy ?: 'stop';
            $model->version = $model->version ?: 1;
            $model->is_active = $model->is_active ?? true;
        });
    }

    public function getHandlerKeyAttribute(): ?string
    {
        return $this->handler ?: $this->action_type;
    }

    public function getActionKeyAttribute(): ?string
    {
        return $this->key ?: $this->action_type;
    }

    public function workflowDefinition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function workflowTransition(): BelongsTo
    {
        return $this->belongsTo(WorkflowTransition::class, 'workflow_transition_id');
    }
}
