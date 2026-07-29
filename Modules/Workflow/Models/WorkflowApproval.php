<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowApproval extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_approvals';

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function approver()
    {
        return $this->belongsTo(\Modules\UserManagement\Models\User::class, 'approver_id');
    }
}
