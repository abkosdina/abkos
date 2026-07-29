<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycRejectionReason extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_rejection_reasons';

    public function request()
    {
        return $this->belongsTo(KycRequest::class, 'kyc_request_id');
    }

    public function operator()
    {
        return $this->belongsTo(\App\Models\User::class, 'operator_id');
    }
}
