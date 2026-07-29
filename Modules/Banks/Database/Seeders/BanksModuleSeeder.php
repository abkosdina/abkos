<?php

namespace Modules\Banks\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BanksModuleSeeder extends Seeder
{
    public function run(): void
    {
        // جلوگیری از اجرای چندباره در یک درخواست
        if (app()->has('banks_module_seeded')) {
            return;
        }
        app()->instance('banks_module_seeded', true);

        if (! Schema::hasTable('banks') || ! Schema::hasTable('loan_products') || ! Schema::hasTable('bank_loan_products')) {
            $this->command?->info('Required tables for bank plans are missing. Run migrations first.');
            return;
        }

        $banks = [
            ['name' => 'بانک ملی ایران', 'code' => 'melli'],
            ['name' => 'بانک ملت', 'code' => 'mellat'],
            ['name' => 'بانک تجارت', 'code' => 'tejarat'],
            ['name' => 'بانک صادرات ایران', 'code' => 'saderat'],
            ['name' => 'بانک پارسیان', 'code' => 'parsian'],
            ['name' => 'بانک پاسارگاد', 'code' => 'pasargad'],
            ['name' => 'بانک شهر', 'code' => 'shahr'],
            ['name' => 'بانک کشاورزی', 'code' => 'keshavarzi'],
            ['name' => 'بانک توسعه تعاون', 'code' => 'tosee_taavon'],
            ['name' => 'پست بانک ایران', 'code' => 'postbank'],
        ];

        $bankPlans = [
            'melli' => [
                [
                    'name' => 'اعتبار ملی',
                    'rates' => ['14%', '23%'],
                    'terms' => [12, 18, 24, 36, 48, 60],
                ],
                [
                    'name' => 'مهربانی',
                    'rates' => ['12%', '15%', '18%'],
                    'terms' => [12, 18, 36, 48],
                ],
            ],
            'mellat' => [
                [
                    'name' => 'طرح رفاه ملت',
                    'rates' => ['13%'],
                    'terms' => [12, 24, 36, 48, 60],
                ],
            ],
        ];

        $now = Carbon::now()->toDateTimeString();

        $rows = array_map(fn($b) => [
            'uuid'       => Str::uuid()->toString(),
            'name'       => $b['name'],
            'slug'       => Str::slug($b['code']),
            'code'       => $b['code'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $banks);

        DB::table('banks')->insertOrIgnore($rows);

        $bankIds = DB::table('banks')->pluck('id', 'code')->all();

        foreach ($bankPlans as $bankCode => $plans) {
            if (! isset($bankIds[$bankCode])) {
                $this->command?->info("Bank code {$bankCode} not found, skipping plans.");
                continue;
            }

            foreach ($plans as $plan) {
                $productSlug = Str::slug($bankCode . ' ' . $plan['name']);
                $loanProduct = DB::table('loan_products')->where('slug', $productSlug)->first();

                $productData = [
                    'bank_id' => $bankIds[$bankCode],
                    'name' => $plan['name'],
                    'slug' => $productSlug,
                    'description' => null,
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

                foreach ($plan['rates'] as $rateValue) {
                    $interestRate = (float) str_replace('%', '', $rateValue);

                    foreach ($plan['terms'] as $term) {
                        $exists = DB::table('bank_loan_products')
                            ->where('loan_product_id', $loanProductId)
                            ->where('name', $plan['name'])
                            ->where('duration_months', $term)
                            ->where('installment_count', $term)
                            ->where('interest_rate', $interestRate)
                            ->exists();

                        if (! $exists) {
                            DB::table('bank_loan_products')->insert([
                                'uuid' => Str::uuid()->toString(),
                                'bank_id' => $bankIds[$bankCode],
                                'loan_product_id' => $loanProductId,
                                'name' => $plan['name'],
                                'duration_months' => $term,
                                'installment_count' => $term,
                                'interest_rate' => $interestRate,
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

        $this->command?->info('Seeded banks and loan plans successfully.');
    }
}