<?php
$projectRoot = dirname(__DIR__);
require $projectRoot . '/vendor/autoload.php';
$app = require $projectRoot . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ids = $app['db']->table('advertisements')->orderBy('created_at','desc')->limit(10)->pluck('id')->toArray();
if (empty($ids)) {
    echo "NO_ROWS\n";
    exit(0);
}

$updated = $app['db']->table('advertisements')->whereIn('id', $ids)->update(['priority' => 3]);
echo "UPDATED_PRIORITY:" . $updated . "\n";
