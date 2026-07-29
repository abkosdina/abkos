<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Province;
use Illuminate\Support\Facades\DB;
$p = Province::where('name', 'تهران')->first();
if (! $p) { echo "no tehran province\n"; exit; }
echo "province id={$p->id} name={$p->name}\n";
$c = $p->cities()->get();
echo "cities count=".count($c) ."\n";
foreach ($c as $city) { echo " - {$city->id} {$city->name} is_capital={$city->is_capital}\n"; }
