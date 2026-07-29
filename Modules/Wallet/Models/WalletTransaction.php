<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class WalletTransaction extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallet_transactions';

    protected $casts = [
        'amount' => 'decimal:8',
        'metadata' => 'array',
        'financial_transaction_id' => 'integer',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function ledgerTransaction()
    {
        return $this->belongsTo(\Modules\Ledger\Models\LedgerTransaction::class, 'ledger_transaction_id');
    }

    public function financialTransaction()
    {
        return $this->belongsTo(FinancialTransaction::class, 'financial_transaction_id');
    }
}
