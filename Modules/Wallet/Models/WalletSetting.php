<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WalletSetting extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallet_settings';

    protected $casts = [
        'value' => 'array',
        'metadata' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
