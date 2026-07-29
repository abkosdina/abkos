<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\UserManagement\Http\Controllers\UserController;

$user = User::where('mobile', '09134576502')->first();
if (! $user) {
    echo "USER_NOT_FOUND\n";
    exit(1);
}

Auth::login($user);
$request = Request::create('/api/v1/users', 'GET', ['page' => 1, 'per_page' => 15]);

$controller = new UserController(app(Modules\UserManagement\Services\UserService::class));
$response = $controller->index($request);

echo $response->getContent();
