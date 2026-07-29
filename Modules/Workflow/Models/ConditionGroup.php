<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;

class ConditionGroup extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'condition_groups';

    protected $casts = [
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    public function definition()
    {
        return $this->belongsTo(ConditionDefinition::class, 'condition_definition_id');
    }

    public function parentGroup()
    {
        return $this->belongsTo(self::class, 'parent_group_id');
    }

    public function childGroups()
    {
        return $this->hasMany(self::class, 'parent_group_id')->orderBy('sort_order');
    }

    public function rules()
    {
        return $this->hasMany(ConditionRule::class)->orderBy('sort_order');
    }
}
