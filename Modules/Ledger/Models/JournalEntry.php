<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class JournalEntry extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'journal_entries';

    protected $casts = [
        'metadata' => 'array',
        'posted_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id');
    }
}
