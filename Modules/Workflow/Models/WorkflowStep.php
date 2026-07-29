<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowStep extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_steps';

    protected $casts = [
        'is_required' => 'boolean',
        'approval_required' => 'boolean',
        'rejection_allowed' => 'boolean',
        'comment_required' => 'boolean',
        'attachment_required' => 'boolean',
    ];

    public function workflowDefinition()
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    public function fromTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'from_step_id');
    }

    public function toTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'to_step_id');
    }

    public function actions()
    {
        return $this->hasMany(WorkflowAction::class);
    }

    public function conditions()
    {
        return $this->hasMany(WorkflowCondition::class);
    }

    public function assignments()
    {
        return $this->hasMany(WorkflowAssignment::class);
    }
}
