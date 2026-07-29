<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowLog extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_logs';

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function actor()
    {
        return $this->belongsTo(\Modules\UserManagement\Models\User::class, 'actor_id');
    }
}
