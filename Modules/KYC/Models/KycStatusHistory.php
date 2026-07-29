<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycStatusHistory extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_status_history';

    public function request()
    {
        return $this->belongsTo(KycRequest::class, 'kyc_request_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
