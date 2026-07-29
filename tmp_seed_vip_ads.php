<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Models\LoanOffer;
use Modules\Banks\Models\Bank;
use Modules\Banks\Models\LoanProduct;

$bank = Bank::query()->first();
$loanProduct = LoanProduct::query()->first();
$users = User::query()->limit(5)->get();

if (! $bank || $users->isEmpty()) {
    fwrite(STDERR, "No bank or user found for seeding.\n");
    exit(1);
}

for ($i = 1; $i <= 10; $i++) {
    $user = $users->get(($i - 1) % $users->count()) ?: $users->first();
    $title = "آگهی VIP فوری {$i}";

    $advertisement = Advertisement::query()->create([
        'uuid' => Str::uuid()->toString(),
        'loan_product_id' => $loanProduct?->id,
        'seller_user_id' => $user->id,
        'title' => $title,
        'description' => "آگهی VIP فوری نمونه شماره {$i}",
        'price' => 10_000_000 + ($i * 1_000_000),
        'currency' => 'IRR',
        'status' => 'Published',
        'visibility' => 'Public',
        'priority' => 3,
        'published_at' => now(),
    ]);

    LoanOffer::query()->create([
        'advertisement_id' => $advertisement->id,
        'bank_id' => $bank->id,
        'loan_plan_id' => $loanProduct?->id,
        'loan_amount' => 10_000_000 + ($i * 1_000_000),
        'sale_price' => 11_000_000 + ($i * 1_000_000),
        'interest_rate' => 12.5,
        'installment_count' => 24,
        'monthly_installment' => 500_000 + ($i * 100_000),
        'vip_guarantee' => true,
        'is_online' => true,
        'is_in_person' => true,
        'escrow_enabled' => true,
        'contract_ready' => true,
        'is_negotiable' => true,
    ]);
}

echo "Created 10 VIP + urgent advertisements.\n";
