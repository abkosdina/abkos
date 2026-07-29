<?php

namespace Modules\Advertisements\DTO;

class LoanOfferDTO
{
    public function __construct(
        public int $bankId,
        public int $loanPlanId,
        public ?int $branchId = null,
        public ?int $loanTypeId = null,
        public ?float $loanAmount = null,
        public ?float $salePrice = null,
        public ?float $interestRate = null,
        public ?int $installmentCount = null,
        public ?float $monthlyInstallment = null,
        public ?float $totalRepayment = null,
        public ?int $remainingInstallments = null,
        public bool $guarantorRequired = false,
        public ?int $guarantorCount = 0,
        public bool $checkRequired = false,
        public bool $promissoryNoteRequired = false,
        public bool $collateralRequired = false,
        public ?float $transferFee = 0,
        public ?float $additionalCost = 0,
        public bool $isNegotiable = false,
        public bool $escrowEnabled = false,
        public bool $vipGuarantee = false,
        public bool $contractReady = false,
        public bool $isOnline = true,
        public bool $isInPerson = true,
    ) {
    }

    public function toArray(): array
    {
        return [
            'bank_id' => $this->bankId,
            'loan_plan_id' => $this->loanPlanId,
            'branch_id' => $this->branchId,
            'loan_type_id' => $this->loanTypeId,
            'loan_amount' => $this->loanAmount,
            'sale_price' => $this->salePrice,
            'interest_rate' => $this->interestRate,
            'installment_count' => $this->installmentCount,
            'monthly_installment' => $this->monthlyInstallment,
            'total_repayment' => $this->totalRepayment,
            'remaining_installments' => $this->remainingInstallments,
            'guarantor_required' => $this->guarantorRequired,
            'guarantor_count' => $this->guarantorCount,
            'check_required' => $this->checkRequired,
            'promissory_note_required' => $this->promissoryNoteRequired,
            'collateral_required' => $this->collateralRequired,
            'transfer_fee' => $this->transferFee,
            'additional_cost' => $this->additionalCost,
            'is_negotiable' => $this->isNegotiable,
            'escrow_enabled' => $this->escrowEnabled,
            'vip_guarantee' => $this->vipGuarantee,
            'contract_ready' => $this->contractReady,
            'is_online' => $this->isOnline,
            'is_in_person' => $this->isInPerson,
        ];
    }
}
