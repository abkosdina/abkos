<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class LedgerEntry extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'ledger_entries';

    protected $casts = [
        'debit' => 'decimal:8',
        'credit' => 'decimal:8',
        'running_balance' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
