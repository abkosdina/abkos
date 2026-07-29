<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Advertisements\Http\Controllers\DiscoveryController;
use Modules\Advertisements\Models\Advertisement;

$service = app(Modules\Advertisements\Services\AdvertisementDiscoveryService::class);

// Test fetching ad #52 by ID
$ad = $service->findById(52);
$ad->load(['user', 'loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city']);

$resource = new \Modules\Advertisements\Resources\AdvertisementDetailResource($ad);
echo json_encode($resource->resolve(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
