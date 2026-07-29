<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycField extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_fields';

    protected $casts = [
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_sensitive' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(KycFieldValue::class);
    }
}
