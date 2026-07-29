<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Advertisements\Models\Advertisement;

$ads = Advertisement::query()->where('priority', 3)->get(['id', 'uuid', 'title', 'priority']);

foreach ($ads as $ad) {
    echo sprintf("%d | %s | %s | Priority: %d\n", $ad->id, $ad->uuid, $ad->title, $ad->priority);
}
