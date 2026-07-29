<?php

namespace Modules\Advertisements\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Enums\AdvertisementVisibility;
use Modules\Banks\Models\Bank;
use Modules\Banks\Models\LoanProduct;

class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition(): array
    {
        $statuses = [
            AdvertisementStatus::Published->value,
            AdvertisementStatus::Approved->value,
            AdvertisementStatus::PendingReview->value,
        ];

        $visibilities = [
            AdvertisementVisibility::Public->value,
            AdvertisementVisibility::Private->value,
        ];

        $title = $this->faker->sentence(4);
        $user = User::factory()->create();
        $bank = Bank::query()->first() ?: Bank::query()->create([
            'name' => 'Test Bank',
            'code' => 'TESTBANK',
            'status' => 'active',
        ]);
        $loanProduct = LoanProduct::query()->first() ?: LoanProduct::query()->create([
            'bank_id' => $bank->id,
            'name' => 'Test Loan Product',
            'slug' => 'test-loan-product',
            'description' => 'Test loan product for advertisements.',
            'currency' => 'IRR',
            'min_amount' => 1000000,
            'max_amount' => 100000000,
            'interest_rate' => 5,
            'duration_months' => 12,
            'status' => 'active',
            'is_public' => true,
        ]);

        return [
            'uuid' => $this->faker->uuid(),
            'seller_user_id' => $user->id,
            'user_id' => $user->id,
            'loan_product_id' => $loanProduct->id,
            'title' => $title,
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->numberBetween(5000000, 500000000),
            'currency' => 'IRR',
            'status' => $this->faker->randomElement($statuses),
            'visibility' => $this->faker->randomElement($visibilities),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];
    }

    public function published(): self
    {
        return $this->state([
            'status' => AdvertisementStatus::Published->value,
            'visibility' => AdvertisementVisibility::Public->value,
            'published_at' => now(),
        ]);
    }
}
