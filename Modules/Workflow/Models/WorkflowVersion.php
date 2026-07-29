<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WorkflowVersion extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_versions';

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function workflowDefinition()
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }
}
