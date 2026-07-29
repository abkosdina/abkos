<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;
use Modules\Workflow\Enums\ApprovalInstanceStepStatus;

class ApprovalInstanceStep extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'approval_instance_steps';

    protected $fillable = [
        'approval_instance_id',
        'approval_step_id',
        'status',
        'required_approval_count',
        'received_approval_count',
        'version',
        'started_at',
        'completed_at',
        'rejected_at',
        'returned_at',
        'expired_at',
        'metadata',
    ];

    protected $casts = [
        'status' => ApprovalInstanceStepStatus::class,
        'required_approval_count' => 'integer',
        'received_approval_count' => 'integer',
        'version' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'returned_at' => 'datetime',
        'expired_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function approvalInstance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class);
    }

    public function approvalStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalStep::class);
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
