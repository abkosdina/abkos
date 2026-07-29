<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycIdentitySnapshot extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_identity_snapshots';

    protected $casts = [
        'birth_date' => 'date',
        'metadata' => 'array',
    ];

    protected $fillable = [
        'uuid',
        'kyc_request_id',
        'first_name',
        'last_name',
        'father_name',
        'national_code',
        'birth_date',
        'birth_place',
        'mobile_number',
        'postal_code',
        'address',
        'metadata',
    ];

    /**
     * Get the KYC request this snapshot belongs to
     */
    public function kycRequest()
    {
        return $this->belongsTo(KycRequest::class, 'kyc_request_id');
    }
}
