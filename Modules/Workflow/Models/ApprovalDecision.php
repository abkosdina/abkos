<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;
use Modules\Workflow\Enums\ApprovalDecision as ApprovalDecisionEnum;

class ApprovalDecision extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'approval_decisions';

    protected $fillable = [
        'approval_instance_id',
        'approval_instance_step_id',
        'approver_id',
        'approver_role',
        'decision',
        'comment',
        'reason',
        'delegated_from',
        'idempotency_key',
        'metadata',
        'decided_at',
    ];

    protected $casts = [
        'decision' => ApprovalDecisionEnum::class,
        'metadata' => 'array',
        'decided_at' => 'datetime',
    ];

    public function approvalInstance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class);
    }

    public function approvalInstanceStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstanceStep::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function delegatedFromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_from');
    }
}
