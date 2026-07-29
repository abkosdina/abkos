<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('mobile', '09134576502')->first();
if (! $user) {
    echo "USER_NOT_FOUND\n";
    exit(1);
}

$roles = $user->getRoleNames()->toArray();
echo 'USER:' . $user->mobile . PHP_EOL;
echo 'ROLES:' . implode(',', $roles) . PHP_EOL;
