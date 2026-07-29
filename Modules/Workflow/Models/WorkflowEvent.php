<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowEvent extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_events';

    protected $casts = [
        'payload' => 'array',
        'triggered_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class);
    }
}
