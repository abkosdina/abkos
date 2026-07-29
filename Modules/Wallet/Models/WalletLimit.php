<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WalletLimit extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallet_limits';

    protected $casts = [
        'daily_deposit' => 'decimal:8',
        'daily_withdrawal' => 'decimal:8',
        'maximum_balance' => 'decimal:8',
        'maximum_transaction' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
