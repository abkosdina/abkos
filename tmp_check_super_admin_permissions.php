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

$roles = $user->roles;
echo 'USER: ' . $user->mobile . PHP_EOL;
echo 'ROLES:' . PHP_EOL;
foreach ($roles as $role) {
    echo ' - ' . $role->name . ' (guard=' . $role->guard_name . ') slug_fa=' . ($role->slug_fa ?? '-') . PHP_EOL;
}

echo 'HAS ROLE Super Admin? ' . ($user->hasRole('Super Admin') ? 'YES' : 'NO') . PHP_EOL;
echo 'HAS ROLE super-admin? ' . ($user->hasRole('super-admin') ? 'YES' : 'NO') . PHP_EOL;

echo 'PERMISSIONS:' . PHP_EOL;
foreach ($user->getAllPermissions() as $permission) {
    echo ' - ' . $permission->name . ' / ' . ($permission->display_name ?? '-') . ' / ' . ($permission->slug_fa ?? '-') . PHP_EOL;
}
