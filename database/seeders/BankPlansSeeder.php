<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BankPlansSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('banks') || ! Schema::hasTable('loan_products') || ! Schema::hasTable('bank_loan_products')) {
            $this->command?->info('Required tables for bank plans are missing. Run migrations first.');
            return;
        }

        $banks = DB::table('banks')->pluck('id', 'code')->all();

        if (empty($banks)) {
            $this->command?->info('No banks found to seed plans for.');
            return;
        }

        $terms = [12, 24, 36, 48];
        $rates = [12.0, 14.0, 16.0];
        $now = Carbon::now()->toDateTimeString();

        foreach ($banks as $bankCode => $bankId) {
            for ($planIndex = 1; $planIndex <= 10; $planIndex++) {
                $planName = sprintf('طرح تستی %s %d', $bankCode, $planIndex);
                $planSlug = Str::slug($bankCode . ' ' . $planName);

                $loanProduct = DB::table('loan_products')->where('slug', $planSlug)->first();

                $productData = [
                    'bank_id' => $bankId,
                    'name' => $planName,
                    'slug' => $planSlug,
                    'description' => "طرح تستی {$planIndex} برای بانک {$bankCode}",
                    'currency' => 'IRR',
                    'min_amount' => 0,
                    'max_amount' => 0,
                    'interest_rate' => 0,
                    'duration_months' => 0,
                    'status' => 'active',
                    'is_public' => true,
                    'updated_at' => $now,
                ];

                if ($loanProduct) {
                    DB::table('loan_products')->where('id', $loanProduct->id)->update($productData);
                    $loanProductId = $loanProduct->id;
                } else {
                    $loanProductId = DB::table('loan_products')->insertGetId(array_merge([
                        'uuid' => Str::uuid()->toString(),
                        'created_at' => $now,
                    ], $productData));
                }

                foreach ($rates as $rate) {
                    foreach ($terms as $term) {
                        $exists = DB::table('bank_loan_products')
                            ->where('loan_product_id', $loanProductId)
                            ->where('name', $planName)
                            ->where('duration_months', $term)
                            ->where('installment_count', $term)
                            ->where('interest_rate', $rate)
                            ->exists();

                        if (! $exists) {
                            DB::table('bank_loan_products')->insert([
                                'uuid' => Str::uuid()->toString(),
                                'bank_id' => $bankId,
                                'loan_product_id' => $loanProductId,
                                'name' => $planName,
                                'duration_months' => $term,
                                'installment_count' => $term,
                                'interest_rate' => $rate,
                                'down_payment_percent' => 0,
                                'status' => 'active',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command?->info('Seeded bank plans for ' . count($banks) . ' banks.');
    }
}
