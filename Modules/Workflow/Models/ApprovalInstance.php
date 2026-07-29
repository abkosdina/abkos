<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;
use Modules\Workflow\Enums\ApprovalStatus;

class ApprovalInstance extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'approval_instances';

    protected $fillable = [
        'workflow_instance_id',
        'approval_definition_id',
        'status',
        'required_approval_count',
        'received_approval_count',
        'version',
        'started_at',
        'completed_at',
        'rejected_at',
        'returned_at',
        'expired_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'required_approval_count' => 'integer',
        'received_approval_count' => 'integer',
        'version' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'returned_at' => 'datetime',
        'expired_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function approvalDefinition(): BelongsTo
    {
        return $this->belongsTo(ApprovalDefinition::class);
    }

    public function approvalInstanceSteps(): HasMany
    {
        return $this->hasMany(ApprovalInstanceStep::class);
    }

    public function approvalDecisions(): HasMany
    {
        return $this->hasMany(ApprovalDecision::class);
    }

    public function approvalDelegations(): HasMany
    {
        return $this->hasMany(ApprovalDelegation::class);
    }
}
