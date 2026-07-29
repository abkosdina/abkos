<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class Account extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounts';

    protected $casts = [
        'metadata' => 'array',
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function currentBalance()
    {
        return $this->hasOne(AccountBalance::class);
    }
}
