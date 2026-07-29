<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowInstanceStep extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_instance_steps';

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class);
    }
}
