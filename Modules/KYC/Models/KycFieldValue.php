<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycFieldValue extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_field_values';

    protected $casts = [
        'value' => 'array',
    ];

    public function field()
    {
        return $this->belongsTo(KycField::class, 'kyc_field_id');
    }

    public function request()
    {
        return $this->belongsTo(KycRequest::class, 'kyc_request_id');
    }
}
