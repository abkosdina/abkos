<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'national_code',
        'father_name',
        'birth_date',
        'gender',
        'email',
        'mobile',
        'avatar',
        'postal_code',
        'address',
        'province',
        'city',
        'preferred_language',
        'timezone',
        'status',
    ];
}
