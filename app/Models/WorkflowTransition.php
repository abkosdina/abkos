<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * WorkflowTransition
 * 
 * Defines an allowed transition from one state to another
 * 
 * Example:
 * - from_state: "pending_review"
 * - to_state: "approved"
 * - required_role: "operator"
 * - key: "approve"
 */
class WorkflowTransition extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();

            if (empty($model->action)) {
                $model->action = $model->key ?: $model->name ?: 'transition';
            }
        });
    }

    protected $table = 'workflow_transitions';

    protected $fillable = [
        'uuid',
        'action',
        'workflow_definition_id',
        'from_state_id',
        'to_state_id',
        'name',
        'key',
        'description',
        'is_active',
        'required_role',
        'required_permission',
        'configuration',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'configuration' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the workflow definition this transition belongs to
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
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
     * Get the workflow instance steps using this transition
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowInstanceStep::class, 'transition_id');
    }

    /**
     * Check if this transition is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get required role(s) as array
     */
    public function getRequiredRoles(): array
    {
        if (!$this->required_role) {
            return [];
        }

        return array_map('trim', explode(',', $this->required_role));
    }

    /**
     * Get required permission(s) as array
     */
    public function getRequiredPermissions(): array
    {
        if (!$this->required_permission) {
            return [];
        }

        return array_map('trim', explode(',', $this->required_permission));
    }
}
