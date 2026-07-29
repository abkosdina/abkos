<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowAssignment extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_assignments';

    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class);
    }
}
