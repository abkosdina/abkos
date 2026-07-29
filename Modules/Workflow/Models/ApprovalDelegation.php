<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;
use Modules\Workflow\Enums\ApprovalDelegationStatus;

class ApprovalDelegation extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'approval_delegations';

    protected $fillable = [
        'approval_instance_id',
        'approval_instance_step_id',
        'delegated_from',
        'delegated_to',
        'reason',
        'starts_at',
        'ends_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => ApprovalDelegationStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function approvalInstance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class);
    }

    public function approvalInstanceStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstanceStep::class);
    }

    public function delegatedFromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_from');
    }

    public function delegatedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
