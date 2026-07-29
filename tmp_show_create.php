<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$driver = DB::connection()->getDriverName();
echo $driver . PHP_EOL;
$sql = DB::select('SHOW CREATE TABLE advertisement_views');
print_r($sql[0]);
