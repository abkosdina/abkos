<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class LedgerTransaction extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'ledger_transactions';

    protected $casts = [
        'total_debit' => 'decimal:8',
        'total_credit' => 'decimal:8',
        'metadata' => 'array',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function entries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
