<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WalletLock extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallet_locks';

    protected $casts = [
        'amount' => 'decimal:8',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
