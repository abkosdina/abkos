<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycProfile extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_profiles';

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(KycFieldValue::class);
    }
}
