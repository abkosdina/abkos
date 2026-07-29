<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Advertisements\Models\Advertisement;
use Illuminate\Support\Facades\DB;

// Update ads 51-60 loan_offers with complete data
$ads = Advertisement::whereBetween('id', [51, 60])->with('loanOffer')->get();

foreach ($ads as $ad) {
    if ($ad->loanOffer) {
        $totalRepayment = intval($ad->loanOffer->monthly_installment) * intval($ad->loanOffer->installment_count);
        $ad->loanOffer->update([
            'branch_id' => 1, // Default to branch 1
            'loan_type_id' => 1, // Default to type 1
            'total_repayment' => (string)$totalRepayment,
            'remaining_installments' => $ad->loanOffer->installment_count, // Initially equal to total
        ]);
        echo "Updated loan_offer {$ad->loanOffer->id} with total_repayment={$totalRepayment}, remaining={$ad->loanOffer->installment_count}\n";
    }
}

// Verify ad #51
echo "\n=== Verification ===\n";
$ad = Advertisement::find(51);
$ad->load(['user', 'loanOffer']);
echo json_encode([
    'id' => $ad->id,
    'title' => $ad->title,
    'seller' => $ad->user,
    'loan_offer' => $ad->loanOffer,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

echo "\nDone! All nullable fields now have sensible defaults.\n";
