<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$service = $app->make(Modules\Advertisements\Services\AdvertisementDiscoveryService::class);
$res = $service->search([]);
echo "DATA_COUNT=" . count($res['data']) . "\n";
echo "META_TOTAL=" . ($res['meta']['total'] ?? 'null') . "\n";
print_r(array_map(fn($a)=>[$a->id,$a->title,$a->loanOffer?->id,$a->loanOffer?->bank?->name,$a->loanOffer?->loanPlan?->name], $res['data']));
