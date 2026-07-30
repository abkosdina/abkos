<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\User;
use App\Models\SiteSetting;
$user = User::where('mobile', '09134576502')->first();
if (! $user) {
    echo "NO_USER\n";
    exit(0);
}
echo 'USER: ' . ($user->name ?? 'no-name') . "\n";
echo 'ROLES: ' . implode(',', $user->getRoleNames()->toArray()) . "\n";
echo 'HAS Super Admin: ' . ($user->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";
echo 'HAS super admin: ' . ($user->hasRole('super admin') ? 'YES' : 'NO') . "\n";
$config = SiteSetting::getValue('sidebar_menu_config', null);
echo 'CONFIG TYPE: ' . gettype($config) . "\n";
echo 'CONFIG VALUE: ' . var_export($config, true) . "\n";
