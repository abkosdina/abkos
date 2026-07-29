<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * WorkflowDefinition
 * 
 * Defines a workflow template that can be used for any entity type
 * (Advertisement, KYC, Order, Withdrawal, etc.)
 * 
 * Example:
 * - name: "Advertisement Approval"
 * - key: "advertisement.approval"
 * - entity_type: "Advertisement"
 * - version: 1
 */
class WorkflowDefinition extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();

            if (empty($model->slug) && ! empty($model->name)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    protected $table = 'workflow_definitions';

    protected $fillable = [
        'name',
        'slug',
        'key',
        'description',
        'entity_type',
        'version',
        'is_active',
        'is_default',
        'configuration',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'version' => 'integer',
    ];

    /**
     * Get the states for this workflow definition
     */
    public function states(): HasMany
    {
        return $this->hasMany(WorkflowState::class);
    }

    /**
     * Get the transitions for this workflow definition
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    /**
     * Get the workflow instances for this definition
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * Get the user who created this workflow definition
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this workflow definition
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Get the initial state for this workflow
     */
    public function getInitialState(): ?WorkflowState
    {
        return $this->states()
            ->where('is_initial', true)
            ->first();
    }

    /**
     * Get all available transitions from a specific state
     */
    public function getTransitionsFromState(WorkflowState $state): \Illuminate\Database\Eloquent\Collection
    {
        return $this->transitions()
            ->where('from_state_id', $state->id)
            ->where('is_active', true)
            ->get();
    }
}
