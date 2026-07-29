<?php

namespace Modules\Workflow\Models;

use App\Models\WorkflowInstance as AppWorkflowInstance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WorkflowInstance extends AppWorkflowInstance
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_instances';

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    public function workflowDefinition()
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function approvals()
    {
        return $this->hasMany(WorkflowApproval::class);
    }

    public function logs()
    {
        return $this->hasMany(WorkflowLog::class);
    }

    public function events()
    {
        return $this->hasMany(WorkflowEvent::class);
    }

    public function instanceSteps()
    {
        return $this->hasMany(WorkflowInstanceStep::class);
    }

    public function entity()
    {
        $modelClass = $this->getEntityModelClass();
        return $this->belongsTo($modelClass, 'entity_id');
    }

    protected function getEntityModelClass(): string
    {
        $entityType = $this->entity_type;

        $mapping = [
            'Advertisement' => \Modules\Advertisements\Models\Advertisement::class,
            'KYC' => \Modules\KYC\Models\KycRequest::class,
            'Order' => \Modules\Orders\Models\Order::class,
            'Withdrawal' => \Modules\Withdrawals\Models\Withdrawal::class,
        ];

        $modelClass = $mapping[$entityType] ?? null;

        return $modelClass && class_exists($modelClass) ? $modelClass : EloquentModel::class;
    }

    public function getEntity()
    {
        $modelClass = $this->getEntityModelClass();

        if (!class_exists($modelClass)) {
            return null;
        }

        try {
            return $modelClass::find($this->entity_id);
        } catch (\Throwable) {
            return null;
        }
    }
}
