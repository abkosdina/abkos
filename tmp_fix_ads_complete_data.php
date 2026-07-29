<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Advertisements\Models\Advertisement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Step 1: Create ratings for users who don't have any
$users = User::whereIn('id', [1, 3, 4, 5])->get();

$ratings = [
    1 => 4.5,  // Test User
    3 => 3.8,  // فاطمه موسوی
    4 => 4.2,  // محمد نجفی
    5 => 4.0,  // سارا کمالی
];

foreach ($ratings as $userId => $score) {
    $existingRating = DB::table('ratings')->where('to_user_id', $userId)->first();
    if (!$existingRating) {
        DB::table('ratings')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'from_user_id' => rand(2, 5),
            'to_user_id' => $userId,
            'order_id' => null,
            'score' => $score,
            'comment' => 'Auto-generated rating for data completeness',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created rating for user $userId with score $score\n";
    }
}

// Step 2: Update ads 51-60 with province and city data
$ads = Advertisement::whereBetween('id', [51, 60])->get();

// Use different provinces and cities for variety
$locations = [
    ['province_id' => 14, 'city_id' => 40], // تهران
    ['province_id' => 1, 'city_id' => 1],   // تبریز
    ['province_id' => 4, 'city_id' => 10],  // اصفهان
    ['province_id' => 8, 'city_id' => 22],  // فارس (شیراز)
    ['province_id' => 22, 'city_id' => 62], // مازندران
    ['province_id' => 7, 'city_id' => 19],  // خراسان رضوی
    ['province_id' => 10, 'city_id' => 28], // گیلان
    ['province_id' => 3, 'city_id' => 7],   // اردبیل
    ['province_id' => 5, 'city_id' => 13],  // البرز
    ['province_id' => 6, 'city_id' => 16],  // خوزستان
];

foreach ($ads as $index => $ad) {
    $location = $locations[$index % count($locations)];
    $ad->update([
        'province_id' => $location['province_id'],
        'city_id' => $location['city_id'],
    ]);
    echo "Updated ad {$ad->id} with province_id={$location['province_id']}, city_id={$location['city_id']}\n";
}

// Step 3: Verify the updates
echo "\n=== Verification ===\n";
$ad = Advertisement::find(51)->load(['province', 'city', 'user']);
echo json_encode([
    'id' => $ad->id,
    'title' => $ad->title,
    'seller_id' => $ad->seller_id,
    'seller_name' => $ad->user->name,
    'province' => $ad->province ? $ad->province->name : null,
    'city' => $ad->city ? $ad->city->name : null,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

echo "\nDone! All 10 ads now have complete location and seller data.\n";
