<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OtpRequest extends Model
{
    protected $fillable = [
        'uuid',
        'mobile',
        'otp_hash',
        'attempt_count',
        'max_attempts',
        'expires_at',
        'verified_at',
        'ip_address',
        'device_information',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $otpRequest): void {
            $otpRequest->uuid ??= (string) Str::uuid();
        });
    }
}
