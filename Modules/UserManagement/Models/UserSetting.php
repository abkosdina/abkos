<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'notification_settings',
        'sms_settings',
        'dark_mode',
        'language',
        'timezone',
        'privacy',
    ];

    protected $casts = [
        'notification_settings' => 'array',
        'sms_settings' => 'array',
        'privacy' => 'array',
    ];
}
