<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;

class UserBankAccount extends Model
{
    protected $fillable = [
        'user_id',
        'iban',
        'bank_name',
        'account_holder_name',
        'account_number',
        'is_default',
        'status',
    ];
}
