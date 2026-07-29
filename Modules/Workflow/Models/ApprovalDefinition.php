<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;
use Modules\Workflow\Enums\ApprovalMode;

class ApprovalDefinition extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'approval_definitions';

    protected $fillable = [
        'workflow_definition_id',
        'name',
        'key',
        'description',
        'approval_mode',
        'required_approval_count',
        'is_active',
        'configuration',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'approval_mode' => ApprovalMode::class,
        'is_active' => 'boolean',
        'required_approval_count' => 'integer',
        'configuration' => 'array',
    ];

    public function workflowDefinition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class);
    }

    public function approvalInstances(): HasMany
    {
        return $this->hasMany(ApprovalInstance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
