<?php

namespace Modules\Advertisements\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanOfferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'bank_id' => $this->bank_id,
            'loan_plan_id' => $this->loan_plan_id,
            'branch_id' => $this->branch_id,
            'loan_type_id' => $this->loan_type_id,
            'loan_amount' => $this->loan_amount,
            'sale_price' => $this->sale_price,
            'interest_rate' => $this->interest_rate,
            'installment_count' => $this->installment_count,
            'monthly_installment' => $this->monthly_installment,
            'total_repayment' => $this->total_repayment,
            'remaining_installments' => $this->remaining_installments,
            'guarantor_required' => (bool) $this->guarantor_required,
            'guarantor_count' => $this->guarantor_count,
            'is_negotiable' => (bool) $this->is_negotiable,
            'escrow_enabled' => (bool) $this->escrow_enabled,
            'vip_guarantee' => (bool) $this->vip_guarantee,
            'contract_ready' => (bool) $this->contract_ready,
            'is_online' => (bool) $this->is_online,
            'is_in_person' => (bool) $this->is_in_person,
        ];
    }
}
