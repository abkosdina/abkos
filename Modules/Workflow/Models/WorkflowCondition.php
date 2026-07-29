<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowCondition extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_conditions';

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function workflowDefinition()
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }
}
