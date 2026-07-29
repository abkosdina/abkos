<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class Wallet extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallets';

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function ledgerAccount()
    {
        return $this->belongsTo(\Modules\Ledger\Models\Account::class, 'ledger_account_id');
    }

    public function balance()
    {
        return $this->hasOne(WalletBalance::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function locks()
    {
        return $this->hasMany(WalletLock::class);
    }

    public function limits()
    {
        return $this->hasMany(WalletLimit::class);
    }

    public function settings()
    {
        return $this->hasMany(WalletSetting::class);
    }
}
