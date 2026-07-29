<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Shared\Base\BaseModel;

class WorkflowState extends BaseModel
{
    use HasFactory;

    protected $table = 'workflow_states';

    protected $casts = [
        'is_initial' => 'boolean',
        'is_final' => 'boolean',
        'is_rejection' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    public function workflowDefinition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    public function fromTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'from_state_id');
    }

    public function toTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'to_state_id');
    }

    public function workflowInstances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'current_state_id');
    }
}
