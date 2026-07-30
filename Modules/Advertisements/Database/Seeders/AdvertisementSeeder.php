<?php

namespace Modules\Advertisements\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Models\LoanOffer;
use Modules\Banks\Models\Bank;
use Modules\Banks\Models\BankLoanProduct;
use Modules\Banks\Models\LoanProduct;
use App\Models\Province;
use App\Models\City;

class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('advertisements') || ! Schema::hasTable('loan_offers') || ! Schema::hasTable('banks')) {
            $this->command?->info('Required advertisement, loan_offers or banks tables are missing. Run migrations first.');
            return;
        }

        $userSeeds = [
            ['name' => 'علی رضایی', 'email' => 'ads_persian_user_1@example.com'],
            ['name' => 'فاطمه موسوی', 'email' => 'ads_persian_user_2@example.com'],
            ['name' => 'محمد نجفی', 'email' => 'ads_persian_user_3@example.com'],
        ];

        $users = collect($userSeeds)->map(function (array $userData) {
            return User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        });

        $banks = Bank::query()
            ->whereIn('code', ['melli', 'mellat', 'saderat', 'parsian', 'pasargad'])
            ->orderBy('code')
            ->get();

        if ($banks->isEmpty()) {
            $banks = Bank::query()->orderBy('code')->get();
        }

        if ($banks->isEmpty()) {
            $this->command?->info('No banks found for advertisement seeding.');
            return;
        }

        if (Schema::hasTable('bank_loan_products')) {
            $planGroups = BankLoanProduct::query()
                ->whereIn('bank_id', $banks->pluck('id')->all())
                ->orderBy('bank_id')
                ->orderBy('name')
                ->get()
                ->groupBy('bank_id');
        } elseif (Schema::hasTable('loan_products')) {
            $planGroups = LoanProduct::query()
                ->whereIn('bank_id', $banks->pluck('id')->all())
                ->orderBy('bank_id')
                ->orderBy('name')
                ->get()
                ->groupBy('bank_id');
        } else {
            $planGroups = collect();
        }

        $provinceIds = Province::query()->pluck('id')->all();
        $cityIds = City::query()->pluck('id')->all();

        $sampleAds = [
            [
                'title' => 'آگهی وام مسکن برای خرید خانه در تهران',
                'description' => 'این آگهی برای ارائه شرایط مناسب وام مسکن با نرخ رقابتی و زمان بازپرداخت انعطاف‌پذیر آماده شده است.',
                'price' => 1_200_000_000,
            ],
            [
                'title' => 'آگهی وام خودرو با پیش‌پرداخت کم و اقساط ساده',
                'description' => 'برای متقاضیان خرید خودرو، این آگهی شرایطی با کارمزد مناسب و بازپرداخت قابل تنظیم ارائه می‌دهد.',
                'price' => 850_000_000,
            ],
            [
                'title' => 'آگهی وام شخصی برای سرمایه‌گذاری و رفع نیاز فوری',
                'description' => 'امکان دریافت وام شخصی با مبلغ مناسب و شرایط روشن برای تامین نیازهای مالی کوتاه‌مدت.',
                'price' => 500_000_000,
            ],
            [
                'title' => 'آگهی وام تجاری برای افتتاح کسب‌وکار جدید',
                'description' => 'این فرصت مناسب برای صاحبان مشاغل کوچک است تا با دریافت وام تجاری، رشد کسب‌وکار خود را آغاز کنند.',
                'price' => 2_000_000_000,
            ],
            [
                'title' => 'آگهی وام آموزشی با شرایط ویژه برای دانشجویان',
                'description' => 'امکان دریافت وام آموزشی با مبلغ مناسب و بازپرداخت مشروط به زمان تحصیل و درآمد.',
                'price' => 300_000_000,
            ],
            [
                'title' => 'آگهی وام بازسازی منزل با تسهیلات آسان',
                'description' => 'برای بازسازی و نوسازی خانه، این آگهی شرایطی شفاف و مناسب برای متقاضیان ارائه می‌کند.',
                'price' => 700_000_000,
            ],
            [
                'title' => 'آگهی وام ازدواج با نرخ مناسب و اقساط بلندمدت',
                'description' => 'این آگهی برای تأمین بخشی از هزینه‌های ازدواج با شرایط بازپرداخت طولانی و قابل مدیریت طراحی شده است.',
                'price' => 600_000_000,
            ],
            [
                'title' => 'آگهی وام درمانی برای پوشش هزینه‌های پزشکی',
                'description' => 'امکان دریافت وام درمانی با مبلغ قابل قبول برای پاسخ به نیازهای فوری درمانی و بستری.',
                'price' => 400_000_000,
            ],
            [
                'title' => 'آگهی وام زمین و مسکن برای سرمایه‌گذاری',
                'description' => 'این پیشنهاد برای خرید زمین یا سرمایه‌گذاری در مسکن با شرایط مناسب و نرخ رقابتی منتشر شده است.',
                'price' => 1_500_000_000,
            ],
            [
                'title' => 'آگهی وام قرض‌الحسنه با بازپرداخت نرم',
                'description' => 'برای افرادی که به کمک مالی سریع و با شرایط ساده نیاز دارند، این آگهی گزینه‌ای مناسب است.',
                'price' => 250_000_000,
            ],
        ];

        foreach ($sampleAds as $index => $adData) {
            $bank = $banks[($index % $banks->count())];
            $planList = $planGroups[$bank->id] ?? collect();
            $plan = $planList->isNotEmpty() ? $planList[($index % $planList->count())] : null;
            $user = $users[($index % $users->count())];
            $createdAt = now()->subDays(10 - $index);

            $loanProductId = null;
            if ($plan instanceof BankLoanProduct) {
                $loanProductId = $plan->loan_product_id;
            } elseif ($plan instanceof LoanProduct) {
                $loanProductId = $plan->id;
            }

            if (! $loanProductId) {
                $loanProductId = LoanProduct::query()
                    ->where('bank_id', $bank->id)
                    ->value('id');
            }

            if (! $loanProductId) {
                $this->command?->info(sprintf('Skipping advertisement %d: no loan product found for bank %s', $index + 1, $bank->name));
                continue;
            }

            $priority = $index < 3 ? 3 : 0;

            $advertisement = Advertisement::query()->updateOrCreate(
                ['title' => $adData['title']],
                [
                    'uuid' => Str::uuid()->toString(),
                    'advertisement_number' => 'ADV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),
                    'loan_product_id' => $loanProductId,
                    'seller_user_id' => $user->id,
                    'user_id' => $user->id,
                    'title' => $adData['title'],
                    'description' => $adData['description'],
                    'price' => $adData['price'],
                    'currency' => 'IRR',
                    'status' => 'Published',
                    'visibility' => 'Public',
                    'priority' => $priority,
                    'published_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );

            $provinceId = $provinceIds[($index % count($provinceIds))] ?? null;
            $cityId = $cityIds[($index % count($cityIds))] ?? null;

            if ($provinceId) {
                $advertisement->province_id = $provinceId;
            }

            if ($cityId) {
                $advertisement->city_id = $cityId;
            }

            if ($provinceId || $cityId) {
                $advertisement->save();
            }

            LoanOffer::query()->updateOrCreate(
                ['advertisement_id' => $advertisement->id],
                [
                    'bank_id' => $bank->id,
                    'loan_plan_id' => $plan?->id,
                    'loan_amount' => $adData['price'],
                    'sale_price' => $adData['price'] + 15_000_000,
                    'interest_rate' => 10.5 + ($index % 3) * 1.5,
                    'installment_count' => [12, 24, 36][($index % 3)],
                    'monthly_installment' => round(($adData['price'] + 15_000_000) / [12, 24, 36][($index % 3)]),
                    'escrow_enabled' => true,
                    'is_online' => $index % 2 === 0,
                    'is_in_person' => $index % 2 !== 0,
                    'is_negotiable' => true,
                    'vip_guarantee' => $index < 3,
                    'contract_ready' => true,
                    'transfer_fee' => 0,
                    'additional_cost' => 0,
                ]
            );
        }

        $this->command?->info('Seeded 10 Persian advertisement listings for 3 test users.');
    }
}
