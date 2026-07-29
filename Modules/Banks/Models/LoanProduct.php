<?php

namespace Modules\Banks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LoanProduct extends Model
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

    protected $table = 'loan_products';

    protected $fillable = [
        'uuid',
        'bank_id',
        'name',
        'slug',
        'description',
        'currency',
        'min_amount',
        'max_amount',
        'interest_rate',
        'duration_months',
        'status',
        'is_public',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'interest_rate' => 'decimal:4',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function bankLoanProducts(): HasMany
    {
        return $this->hasMany(BankLoanProduct::class, 'loan_product_id');
    }
}
