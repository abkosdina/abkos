<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;

class ConditionRule extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'condition_rules';

    protected $casts = [
        'expected_value' => 'json',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    public function definition()
    {
        return $this->belongsTo(ConditionDefinition::class, 'condition_definition_id');
    }

    public function group()
    {
        return $this->belongsTo(ConditionGroup::class, 'condition_group_id');
    }
}
