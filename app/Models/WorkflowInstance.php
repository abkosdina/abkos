<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * WorkflowInstance
 * 
 * A workflow instance represents one workflow process for one business entity.
 * 
 * Example:
 * - Advertisement #100 + Advertisement Approval workflow = One WorkflowInstance
 * - KYC #50 + KYC Verification workflow = One WorkflowInstance
 * 
 * The instance tracks:
 * - Which workflow definition is being used
 * - What entity is being processed
 * - Current state of the workflow
 * - Version for optimistic locking
 * - When it was started/completed
 */
class WorkflowInstance extends EloquentModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'workflow_instances';

    protected $fillable = [
        'workflow_definition_id',
        'entity_type',
        'entity_id',
        'current_state_id',
        'status',
        'version',
        'started_at',
        'completed_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'version' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the workflow definition
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    /**
     * Get the current state
     */
    public function currentState(): BelongsTo
    {
        return $this->belongsTo(WorkflowState::class, 'current_state_id');
    }

    /**
     * Get all steps in this workflow instance
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowInstanceStep::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the business entity this workflow is for
     * 
     * Example: Get the Advertisement model for this workflow instance
     * $advertisement = $instance->entity;
     */
    public function entity()
    {
        $modelClass = $this->getEntityModelClass();
        return $this->belongsTo($modelClass, 'entity_id');
    }

    /**
     * Get the model class for this entity type
     */
    protected function getEntityModelClass(): string
    {
        $entityType = $this->entity_type;

        // Map entity types to model classes
        $mapping = [
            'Advertisement' => \Modules\Advertisements\Models\Advertisement::class,
            'KYC' => \Modules\KYC\Models\KycRequest::class,
            'Order' => \Modules\Orders\Models\Order::class,
            'Withdrawal' => \Modules\Withdrawals\Models\Withdrawal::class,
        ];

        $modelClass = $mapping[$entityType] ?? null;

        return $modelClass && class_exists($modelClass) ? $modelClass : EloquentModel::class;
    }

    /**
     * Get a model instance of the entity
     */
    public function getEntity()
    {
        $modelClass = $this->getEntityModelClass();

        if (!class_exists($modelClass)) {
            return null;
        }

        try {
            return $modelClass::find($this->entity_id);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Check if workflow is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if workflow is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if workflow is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if current state is final
     */
    public function isInFinalState(): bool
    {
        return $this->currentState->is_final ?? false;
    }

    /**
     * Get available transitions from current state
     */
    public function getAvailableTransitions()
    {
        return $this->definition
            ->getTransitionsFromState($this->currentState);
    }
}
