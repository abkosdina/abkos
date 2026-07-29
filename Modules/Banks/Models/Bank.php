<?php

namespace Modules\Banks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Bank extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->slug) && ! empty($model->name)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'code',
        'status',
        'country',
        'address',
        'contact_email',
        'contact_phone',
        'is_verified',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(BankEmployee::class);
    }

    public function loanProducts(): HasMany
    {
        return $this->hasMany(LoanProduct::class);
    }

    public function bankLoanProducts(): HasMany
    {
        return $this->hasMany(BankLoanProduct::class, 'bank_id');
    }
}
