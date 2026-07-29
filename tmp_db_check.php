<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Modules\Advertisements\Models\Advertisement;

$tables = ['banks', 'loan_products', 'bank_loan_products', 'advertisements', 'loan_offers'];
foreach ($tables as $table) {
    $exists = DB::select("SHOW TABLES LIKE '$table'");
    echo "$table exists=" . (!empty($exists) ? 'yes' : 'no') . "\n";
    if (!empty($exists)) {
        $count = DB::table($table)->count();
        echo "$table count=$count\n";
    }
}

$ad = Advertisement::with('loanOffer.bank','loanOffer.loanPlan')->first();
if (! $ad) {
    echo 'NO_AD_FOUND\n';
    exit(0);
}
$lo = $ad->loanOffer;
echo "AD={$ad->id} UUID={$ad->uuid} TITLE={$ad->title}\n";
echo "LO=" . ($lo?->id ?? 'null') . " bank_id=" . ($lo?->bank_id ?? 'null') . " loan_plan_id=" . ($lo?->loan_plan_id ?? 'null') . "\n";
echo "BANK=" . ($lo?->bank?->id ?? 'null') . " name=" . ($lo?->bank?->name ?? 'null') . "\n";
echo "PLAN=" . ($lo?->loanPlan?->id ?? 'null') . " name=" . ($lo?->loanPlan?->name ?? 'null') . "\n";
