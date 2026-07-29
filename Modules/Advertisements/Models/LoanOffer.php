<?php

namespace Modules\Advertisements\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Modules\Advertisements\Database\Factories\LoanOfferFactory;
use Modules\Banks\Models\Bank;
use Modules\Banks\Models\BankLoanProduct;
use Modules\Banks\Models\LoanProduct;

class LoanOffer extends Model
{
    use HasFactory;
    protected $fillable = [
        'advertisement_id',
        'bank_id',
        'loan_plan_id',
        'branch_id',
        'loan_type_id',
        'loan_amount',
        'sale_price',
        'interest_rate',
        'installment_count',
        'monthly_installment',
        'total_repayment',
        'remaining_installments',
        'guarantor_required',
        'guarantor_count',
        'check_required',
        'promissory_note_required',
        'collateral_required',
        'transfer_fee',
        'additional_cost',
        'is_negotiable',
        'escrow_enabled',
        'vip_guarantee',
        'contract_ready',
        'is_online',
        'is_in_person',
    ];

    public function advertisement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function loanPlan(): BelongsTo
    {
        if (Schema::hasTable('bank_loan_products')) {
            return $this->belongsTo(BankLoanProduct::class, 'loan_plan_id');
        }

        return $this->belongsTo(LoanProduct::class, 'loan_plan_id');
    }

    protected static function newFactory()
    {
        return LoanOfferFactory::new();
    }
}
