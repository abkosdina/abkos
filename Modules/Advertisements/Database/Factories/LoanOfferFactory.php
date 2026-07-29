<?php

namespace Modules\Advertisements\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Advertisements\Models\LoanOffer;
use Modules\Advertisements\Models\Advertisement;

class LoanOfferFactory extends Factory
{
    protected $model = LoanOffer::class;

    public function definition(): array
    {
        $loanAmount = $this->faker->numberBetween(5000000, 500000000);
        $salePrice = (int) ($loanAmount * $this->faker->randomFloat(2, 0.7, 0.95));
        $interestRate = $this->faker->randomFloat(2, 0.5, 15);
        $installmentCount = $this->faker->randomElement([6, 12, 24, 36, 48, 60]);
        $monthlyInstallment = (int) ($salePrice / $installmentCount);

        return [
            'advertisement_id' => Advertisement::factory(),
            'bank_id' => $this->faker->numberBetween(1, 10),
            'loan_plan_id' => $this->faker->numberBetween(1, 20),
            'branch_id' => $this->faker->numberBetween(1, 50),
            'loan_type_id' => $this->faker->numberBetween(1, 5),
            'loan_amount' => $loanAmount,
            'sale_price' => $salePrice,
            'interest_rate' => $interestRate,
            'installment_count' => $installmentCount,
            'monthly_installment' => $monthlyInstallment,
            'total_repayment' => $salePrice,
            'remaining_installments' => $installmentCount,
            'guarantor_required' => $this->faker->boolean(30),
            'guarantor_count' => $this->faker->numberBetween(0, 3),
            'check_required' => $this->faker->boolean(60),
            'promissory_note_required' => $this->faker->boolean(40),
            'collateral_required' => $this->faker->boolean(50),
            'transfer_fee' => 0,
            'additional_cost' => $this->faker->numberBetween(0, 100000),
            'is_negotiable' => $this->faker->boolean(50),
            'escrow_enabled' => $this->faker->boolean(80),
            'vip_guarantee' => $this->faker->boolean(20),
            'contract_ready' => true,
            'is_online' => $this->faker->boolean(70),
            'is_in_person' => $this->faker->boolean(30),
        ];
    }
}
