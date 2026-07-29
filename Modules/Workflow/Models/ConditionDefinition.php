<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;

class ConditionDefinition extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'condition_definitions';

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
        'configuration' => 'array',
        'metadata' => 'array',
    ];

    public function groups()
    {
        return $this->hasMany(ConditionGroup::class)->orderBy('sort_order');
    }

    public function rules()
    {
        return $this->hasMany(ConditionRule::class)->orderBy('sort_order');
    }

    public function evaluations()
    {
        return $this->hasMany(ConditionEvaluation::class);
    }
}
