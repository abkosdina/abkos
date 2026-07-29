<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class AccountType extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'account_types';

    protected $casts = [
        'metadata' => 'array',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
