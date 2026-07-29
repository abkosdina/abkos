<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowTransition extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_transitions';

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function workflowDefinition()
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    public function fromStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'from_step_id');
    }

    public function toStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'to_step_id');
    }

    public function condition()
    {
        return $this->belongsTo(WorkflowCondition::class);
    }
}
