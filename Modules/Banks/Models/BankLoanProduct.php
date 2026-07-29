<?php

namespace Modules\Banks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BankLoanProduct extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $table = 'bank_loan_products';

    protected $fillable = [
        'uuid',
        'bank_id',
        'loan_product_id',
        'name',
        'duration_months',
        'installment_count',
        'interest_rate',
        'down_payment_percent',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:4',
        'down_payment_percent' => 'decimal:2',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }
}
