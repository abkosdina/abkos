<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WalletBalance extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallet_balances';

    protected $casts = [
        'available_balance' => 'decimal:8',
        'blocked_balance' => 'decimal:8',
        'pending_balance' => 'decimal:8',
        'total_balance' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
