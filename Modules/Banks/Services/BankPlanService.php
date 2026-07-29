<?php

namespace Modules\Banks\Services;

use Illuminate\Support\Collection;

class BankPlanService
{
    /**
     * Group bank plan rows by bank code for UI/API consumption.
     */
    public function groupPlansByBankCode(Collection $banks, Collection $plans): array
    {
        $bankCodes = $banks->pluck('code')->filter()->values()->toArray();
        $grouped = array_fill_keys($bankCodes, []);

        foreach ($plans as $plan) {
            if (! isset($plan->bank_code) || ! in_array($plan->bank_code, $bankCodes, true)) {
                continue;
            }

            // Normalize plan into an object with common keys
            $planObj = [
                'id' => $plan->id ?? null,
                'name' => $plan->name ?? null,
                'interest_rate' => $plan->interest_rate ?? ($plan->interestRate ?? null),
                'duration_months' => $plan->duration_months ?? ($plan->durationMonths ?? null),
                'installment_count' => $plan->installment_count ?? ($plan->installmentCount ?? null),
                'down_payment_percent' => $plan->down_payment_percent ?? ($plan->downPaymentPercent ?? null),
                'description' => $plan->description ?? null,
            ];

            $grouped[$plan->bank_code][] = (object) array_filter($planObj, static fn ($v) => $v !== null && $v !== '');
        }

        // Remove empty groups
        foreach ($grouped as $bankCode => $items) {
            if (empty($items)) unset($grouped[$bankCode]);
        }

        return $grouped;
    }
}
