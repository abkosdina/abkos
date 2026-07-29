<?php

namespace Modules\Advertisements\Tests\Feature;

use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Modules\Advertisements\Models\LoanOffer;
use Modules\Banks\Models\Bank;
use Modules\Banks\Models\BankLoanProduct;
use Modules\Banks\Models\LoanProduct;
use Tests\TestCase;

class AdvertisementCreateTest extends TestCase
{
    public function test_create_advertisement(): void
    {
        $this->withoutMiddleware();

        if (! Schema::hasTable('provinces') || ! Schema::hasTable('cities')) {
            $this->markTestSkipped('Location tables are not available in this test environment.');
        }

        $province = Province::query()->create([
            'name' => 'Test Province',
            'name_en' => 'Test Province',
            'slug' => 'test-province',
        ]);
        $city = City::query()->create([
            'province_id' => $province->id,
            'name' => 'Test City',
            'name_en' => 'Test City',
            'slug' => 'test-city',
            'is_capital' => false,
        ]);

        $user = User::factory()->create();

        $payload = [
            'title' => 'Test Ad',
            'description' => 'Detailed description',
            'province_id' => $province->id,
            'city_id' => $city->id,
            // satisfy legacy/alternate schemas used in test DB
            'loan_product_id' => 1,
            'visibility' => 'Public',
            'priority' => 0,
            'loan_offer' => [
                'bank_id' => 1,
                'loan_plan_id' => 1,
                'loan_type_id' => 1,
                'loan_amount' => 10000,
                'sale_price' => 9000,
                'interest_rate' => 5,
                'installment_count' => 12,
                'monthly_installment' => 800,
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/advertisements', $payload);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_create_advertisement_respects_daily_creation_limit(): void
    {
        $this->withoutMiddleware();

        if (! Schema::hasTable('provinces') || ! Schema::hasTable('cities')) {
            $this->markTestSkipped('Location tables are not available in this test environment.');
        }

        Config::set('advertisements.limits.daily_creation_per_user', 1);

        $province = Province::query()->create([
            'name' => 'Test Province',
            'name_en' => 'Test Province',
            'slug' => 'test-province',
        ]);
        $city = City::query()->create([
            'province_id' => $province->id,
            'name' => 'Test City',
            'name_en' => 'Test City',
            'slug' => 'test-city',
            'is_capital' => false,
        ]);

        $user = User::factory()->create();

        $payload = [
            'title' => 'Test Ad Limit',
            'description' => 'Detailed description',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'loan_product_id' => 1,
            'visibility' => 'Public',
            'priority' => 0,
            'loan_offer' => [
                'bank_id' => 1,
                'loan_plan_id' => 1,
                'loan_type_id' => 1,
                'loan_amount' => 10000,
                'sale_price' => 9000,
                'interest_rate' => 5,
                'installment_count' => 12,
                'monthly_installment' => 800,
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/advertisements', $payload);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $secondResponse = $this->actingAs($user, 'sanctum')->postJson('/api/advertisements', $payload);
        $secondResponse->assertStatus(422);
        $secondResponse->assertJsonPath('success', false);
        $secondResponse->assertJsonPath('message', 'Daily advertisement creation limit reached (1 per day).');
    }

    public function test_create_advertisement_ignores_manual_interest_rate_override(): void
    {
        $this->withoutMiddleware();

        if (! Schema::hasTable('provinces') || ! Schema::hasTable('cities')) {
            $this->markTestSkipped('Location tables are not available in this test environment.');
        }

        $province = Province::query()->create([
            'name' => 'Test Province',
            'name_en' => 'Test Province',
            'slug' => 'test-province',
        ]);
        $city = City::query()->create([
            'province_id' => $province->id,
            'name' => 'Test City',
            'name_en' => 'Test City',
            'slug' => 'test-city',
            'is_capital' => false,
        ]);

        $user = User::factory()->create();

        $planInterest = 12.5;
        if (Schema::hasTable('bank_loan_products')) {
            $bank = Bank::query()->create(['name' => 'Test Bank', 'code' => 'TB', 'status' => 'active']);
            $loanProduct = LoanProduct::query()->create([
                'bank_id' => $bank->id,
                'name' => 'Test Loan Product',
                'slug' => 'test-loan-product',
                'description' => 'Test product',
                'currency' => 'IRR',
                'min_amount' => 1000000,
                'max_amount' => 100000000,
                'interest_rate' => $planInterest,
                'duration_months' => 12,
                'status' => 'active',
                'is_public' => true,
            ]);
            $plan = BankLoanProduct::query()->create([
                'bank_id' => $bank->id,
                'loan_product_id' => $loanProduct->id,
                'name' => 'Test Plan',
                'duration_months' => 12,
                'installment_count' => 12,
                'interest_rate' => $planInterest,
                'down_payment_percent' => 0,
                'status' => 'active',
            ]);
        } else {
            $bank = Bank::query()->create(['name' => 'Test Bank', 'code' => 'TB', 'status' => 'active']);
            $plan = LoanProduct::query()->create([
                'bank_id' => $bank->id,
                'name' => 'Test Plan',
                'slug' => 'test-plan',
                'description' => 'Test plan',
                'currency' => 'IRR',
                'min_amount' => 1000000,
                'max_amount' => 100000000,
                'interest_rate' => $planInterest,
                'duration_months' => 12,
                'status' => 'active',
                'is_public' => true,
            ]);
        }

        $payload = [
            'title' => 'Test Ad',
            'description' => 'Detailed description',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'loan_product_id' => $plan->loan_product_id ?? $plan->id,
            'visibility' => 'Public',
            'priority' => 0,
            'loan_offer' => [
                'bank_id' => $bank->id,
                'loan_plan_id' => $plan->id,
                'loan_type_id' => 1,
                'loan_amount' => 10000,
                'sale_price' => 9000,
                'interest_rate' => 99,
                'installment_count' => 5,
                'monthly_installment' => 1800,
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/advertisements', $payload);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $adId = data_get($response->json('data'), 'advertisement.id');
        $this->assertDatabaseHas('loan_offers', [
            'advertisement_id' => $adId,
            'interest_rate' => $planInterest,
            'installment_count' => 12,
        ]);
    }

    public function test_create_advertisement_uses_loan_offer_plan_as_fallback_for_loan_product_id(): void
    {
        $this->withoutMiddleware();

        if (! Schema::hasTable('provinces') || ! Schema::hasTable('cities')) {
            $this->markTestSkipped('Location tables are not available in this test environment.');
        }

        $province = Province::query()->create([
            'name' => 'Province A',
            'name_en' => 'Province A',
            'slug' => 'province-a',
        ]);
        $city = City::query()->create([
            'province_id' => $province->id,
            'name' => 'City A',
            'name_en' => 'City A',
            'slug' => 'city-a',
            'is_capital' => false,
        ]);

        $user = User::factory()->create();
        $bank = Bank::query()->create(['name' => 'Fallback Bank', 'code' => 'FB', 'status' => 'active']);
        $loanProduct = LoanProduct::query()->create([
            'bank_id' => $bank->id,
            'name' => 'Fallback Loan Product',
            'slug' => 'fallback-loan-product',
            'description' => 'Fallback product',
            'currency' => 'IRR',
            'min_amount' => 1000000,
            'max_amount' => 100000000,
            'interest_rate' => 8,
            'duration_months' => 12,
            'status' => 'active',
            'is_public' => true,
        ]);
        $plan = BankLoanProduct::query()->create([
            'bank_id' => $bank->id,
            'loan_product_id' => $loanProduct->id,
            'name' => 'Fallback Plan',
            'duration_months' => 12,
            'installment_count' => 12,
            'interest_rate' => 8,
            'down_payment_percent' => 0,
            'status' => 'active',
        ]);

        $payload = [
            'title' => 'Fallback Product Ad',
            'description' => 'This should succeed without explicit loan_product_id',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'visibility' => 'Public',
            'priority' => 0,
            'loan_offer' => [
                'bank_id' => $bank->id,
                'loan_plan_id' => $plan->id,
                'loan_type_id' => 1,
                'loan_amount' => 10000,
                'sale_price' => 9000,
                'interest_rate' => 8,
                'installment_count' => 12,
                'monthly_installment' => 750,
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/advertisements', $payload);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $adId = data_get($response->json('data'), 'advertisement.id');
        $this->assertDatabaseHas('advertisements', [
            'id' => $adId,
            'loan_product_id' => $loanProduct->id,
        ]);
    }

    public function test_create_advertisement_rejects_city_from_other_province(): void
    {
        $this->withoutMiddleware();

        if (! Schema::hasTable('provinces') || ! Schema::hasTable('cities')) {
            $this->markTestSkipped('Location tables are not available in this test environment.');
        }

        $province = Province::query()->create([
            'name' => 'Province A',
            'name_en' => 'Province A',
            'slug' => 'province-a',
        ]);
        $otherProvince = Province::query()->create([
            'name' => 'Province B',
            'name_en' => 'Province B',
            'slug' => 'province-b',
        ]);
        $city = City::query()->create([
            'province_id' => $otherProvince->id,
            'name' => 'Other City',
            'name_en' => 'Other City',
            'slug' => 'other-city',
            'is_capital' => false,
        ]);

        $user = User::factory()->create();

        $payload = [
            'title' => 'Invalid Location Ad',
            'description' => 'This should fail',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'visibility' => 'Public',
            'priority' => 0,
            'loan_offer' => [
                'bank_id' => 1,
                'loan_plan_id' => 1,
                'loan_type_id' => 1,
                'loan_amount' => 10000,
                'sale_price' => 9000,
                'interest_rate' => 5,
                'installment_count' => 12,
                'monthly_installment' => 800,
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/advertisements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city_id']);
    }
}
