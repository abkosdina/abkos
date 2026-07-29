<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class FinancialPeriod extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'financial_periods';

    protected $casts = [
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function snapshots()
    {
        return $this->hasMany(BalanceSnapshot::class, 'period_id');
    }
}
