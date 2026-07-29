<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * WorkflowState
 * 
 * A possible state within a workflow definition
 * 
 * Example states for Advertisement workflow:
 * - draft
 * - pending_review
 * - approved
 * - published
 * - rejected
 * - archived
 */
class WorkflowState extends Model
{
    use HasFactory;

    protected $table = 'workflow_states';

    protected $fillable = [
        'workflow_definition_id',
        'name',
        'key',
        'description',
        'is_initial',
        'is_final',
        'is_rejection',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_initial' => 'boolean',
        'is_final' => 'boolean',
        'is_rejection' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the workflow definition this state belongs to
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    /**
     * Get transitions FROM this state
     */
    public function fromTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'from_state_id');
    }

    /**
     * Get transitions TO this state
     */
    public function toTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'to_state_id');
    }

    /**
     * Get workflow instances in this state
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'current_state_id');
    }

    /**
     * Check if this is a terminal state
     */
    public function isFinal(): bool
    {
        return $this->is_final;
    }

    /**
     * Check if this is an initial state
     */
    public function isInitial(): bool
    {
        return $this->is_initial;
    }

    /**
     * Check if this is a rejection state
     */
    public function isRejection(): bool
    {
        return $this->is_rejection;
    }
}
