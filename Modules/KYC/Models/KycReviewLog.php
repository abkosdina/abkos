<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycReviewLog extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_review_logs';

    public function request()
    {
        return $this->belongsTo(KycRequest::class, 'kyc_request_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }
}
