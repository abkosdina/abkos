<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class FinancialTransaction extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'financial_transactions';

    protected $casts = [
        'amount' => 'decimal:8',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function senderWallet()
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id');
    }

    public function receiverWallet()
    {
        return $this->belongsTo(Wallet::class, 'receiver_wallet_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function ledgerTransaction()
    {
        return $this->belongsTo(\Modules\Ledger\Models\LedgerTransaction::class, 'ledger_transaction_id');
    }
}
