<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Shared\Base\BaseModel;

class ConditionEvaluation extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
    }

    protected $table = 'condition_evaluations';

    protected $casts = [
        'passed' => 'boolean',
        'metadata' => 'array',
        'result_payload' => 'array',
    ];

    public function definition()
    {
        return $this->belongsTo(ConditionDefinition::class, 'condition_definition_id');
    }
}
