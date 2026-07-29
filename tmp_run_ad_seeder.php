<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Advertisements\Database\Seeders\AdvertisementSeeder;

$seeder = new AdvertisementSeeder();
$seeder->run();
echo "Seeder finished\n";
