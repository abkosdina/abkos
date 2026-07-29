<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;

class ApprovalStep extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'approval_steps';

    protected $fillable = [
        'approval_definition_id',
        'name',
        'key',
        'description',
        'sort_order',
        'required_role',
        'required_permission',
        'required_user_id',
        'required_approval_count',
        'is_mandatory',
        'can_reject',
        'can_return_for_correction',
        'can_delegate',
        'expires_after_minutes',
        'configuration',
        'metadata',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'required_approval_count' => 'integer',
        'is_mandatory' => 'boolean',
        'can_reject' => 'boolean',
        'can_return_for_correction' => 'boolean',
        'can_delegate' => 'boolean',
        'expires_after_minutes' => 'integer',
        'configuration' => 'array',
        'metadata' => 'array',
    ];

    public function approvalDefinition(): BelongsTo
    {
        return $this->belongsTo(ApprovalDefinition::class);
    }

    public function approvalInstanceSteps(): HasMany
    {
        return $this->hasMany(ApprovalInstanceStep::class);
    }

    public function requiredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'required_user_id');
    }
}
