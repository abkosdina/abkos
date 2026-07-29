<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

echo 'db.default=' . Config::get('database.default') . "\n";
echo 'db.database=' . Config::get('database.connections.' . Config::get('database.default') . '.database') . "\n";
echo 'db.host=' . Config::get('database.connections.' . Config::get('database.default') . '.host') . "\n";
echo 'db.user=' . Config::get('database.connections.' . Config::get('database.default') . '.username') . "\n";
try {
    $res = DB::select('select database() as db');
    echo 'select_database=' . ($res[0]->db ?? 'null') . "\n";
} catch (Exception $e) {
    echo 'db_error=' . $e->getMessage() . "\n";
}
