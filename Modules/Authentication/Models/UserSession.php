<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserSession extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'token_id',
        'ip_address',
        'user_agent',
        'last_activity_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }
}
