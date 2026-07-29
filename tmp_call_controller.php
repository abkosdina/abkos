<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\LocationController;
use Illuminate\Http\Request;
use App\Models\Province;

$controller = new LocationController();
$province = Province::find(8);
$res = $controller->getCitiesByProvince(Request::create('/','GET'), $province);
// $res might be a ResourceCollection; get data
if (method_exists($res, 'response')) {
    echo $res->response()->getContent();
} else {
    echo json_encode($res);
}

