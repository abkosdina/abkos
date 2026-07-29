<?php

namespace Modules\Advertisements\Services;

use App\Models\City;
use Illuminate\Support\Arr;
use Modules\Advertisements\DTO\AdvertisementDTO;
use Modules\Advertisements\DTO\LoanOfferDTO;
use Modules\Shared\Services\LocationService;

class AdvertisementValidationService
{
    public function validateCreate(AdvertisementDTO $dto): void
    {
        $this->assertRequired($dto, ['title', 'description', 'provinceId', 'cityId', 'loanOffer']);

        if ($dto->provinceId && $dto->cityId) {
            $city = City::query()->find($dto->cityId);
            if ($city && (int) $city->province_id !== (int) $dto->provinceId) {
                throw new \InvalidArgumentException('The selected city does not belong to the selected province.');
            }
        }

        if ($dto->loanOffer instanceof LoanOfferDTO) {
            $this->assertRequired($dto->loanOffer, [
                'bankId',
                'loanPlanId',
                'loanAmount',
                'salePrice',
            ]);

            if ($dto->loanOffer->interestRate === null || $dto->loanOffer->interestRate === '') {
                $dto->loanOffer->interestRate = $this->resolveInterestRateFromPlan($dto->loanOffer->loanPlanId);
            }

            if ($dto->loanOffer->installmentCount === null || $dto->loanOffer->installmentCount === '') {
                $dto->loanOffer->installmentCount = $this->resolveInstallmentCountFromPlan($dto->loanOffer->loanPlanId);
            }

            if ($dto->loanOffer->monthlyInstallment === null || $dto->loanOffer->monthlyInstallment === '') {
                $dto->loanOffer->monthlyInstallment = $this->resolveMonthlyInstallment($dto->loanOffer);
            }

            if ($dto->loanOffer->loanTypeId === null || $dto->loanOffer->loanTypeId === '') {
                $dto->loanOffer->loanTypeId = 1;
            }
        }
    }

    private function assertRequired(object $source, array $fields): void
    {
        foreach ($fields as $field) {
            if (is_array($source)) {
                $value = Arr::get($source, $field);
            } elseif (is_object($source)) {
                $value = property_exists($source, $field) ? $source->{$field} : Arr::get($source, $field);
            } else {
                $value = Arr::get($source, $field);
            }
            if ($value === null || $value === '' || $value === []) {
                throw new \InvalidArgumentException(sprintf('The field "%s" is required.', $field));
            }
        }
    }

    private function resolveInterestRateFromPlan(?int $loanPlanId): ?float
    {
        if (empty($loanPlanId)) {
            return null;
        }

        try {
            if (class_exists(\Modules\Banks\Models\BankLoanProduct::class) && \Illuminate\Support\Facades\Schema::hasTable('bank_loan_products')) {
                $plan = \Modules\Banks\Models\BankLoanProduct::query()->find($loanPlanId);
                if ($plan && $plan->interest_rate !== null) {
                    return (float) $plan->interest_rate;
                }
            }

            if (class_exists(\Modules\Banks\Models\LoanProduct::class) && \Illuminate\Support\Facades\Schema::hasTable('loan_products')) {
                $plan = \Modules\Banks\Models\LoanProduct::query()->find($loanPlanId);
                if ($plan && $plan->interest_rate !== null) {
                    return (float) $plan->interest_rate;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function resolveInstallmentCountFromPlan(?int $loanPlanId): ?int
    {
        if (empty($loanPlanId)) {
            return null;
        }

        try {
            if (class_exists(\Modules\Banks\Models\BankLoanProduct::class) && \Illuminate\Support\Facades\Schema::hasTable('bank_loan_products')) {
                $plan = \Modules\Banks\Models\BankLoanProduct::query()->find($loanPlanId);
                if ($plan && $plan->installment_count !== null) {
                    return (int) $plan->installment_count;
                }
            }

            if (class_exists(\Modules\Banks\Models\LoanProduct::class) && \Illuminate\Support\Facades\Schema::hasTable('loan_products')) {
                $plan = \Modules\Banks\Models\LoanProduct::query()->find($loanPlanId);
                if ($plan && $plan->installment_count !== null) {
                    return (int) $plan->installment_count;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function resolveMonthlyInstallment(LoanOfferDTO $loanOffer): ?float
    {
        if ($loanOffer->salePrice === null || $loanOffer->salePrice === '' || $loanOffer->installmentCount === null || $loanOffer->installmentCount === '') {
            return null;
        }

        return (float) round($loanOffer->salePrice / $loanOffer->installmentCount);
    }
}
