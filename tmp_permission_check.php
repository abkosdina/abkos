<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

$role = Role::where('name', 'Super Admin')->first();
if ($role) {
    echo 'ROLE: ' . $role->name . ' guard=' . $role->guard_name . PHP_EOL;
    echo 'ROLE PERMS:' . PHP_EOL;
    foreach ($role->permissions as $perm) {
        echo ' - ' . $perm->name . ' / ' . ($perm->display_name ?? '-') . PHP_EOL;
    }
    echo 'ROLE PERM COUNT: ' . $role->permissions->count() . PHP_EOL;
}

$user = User::where('mobile', '09134576502')->first();
if ($user) {
    echo 'USER: ' . $user->name . ' (' . $user->mobile . ')' . PHP_EOL;
    echo 'USER ROLES: ' . implode(',', $user->getRoleNames()->toArray()) . PHP_EOL;
    echo 'USER PERMS: ' . implode(',', $user->getPermissionNames()->toArray()) . PHP_EOL;
    echo 'CAN menu.users? ' . ($user->can('menu.users') ? 'YES' : 'NO') . PHP_EOL;
    echo 'HAS menu.users? ' . ($user->hasPermissionTo('menu.users') ? 'YES' : 'NO') . PHP_EOL;
}

echo 'PERMISSIONS TOTAL: ' . Permission::count() . PHP_EOL;
echo 'ROLES TOTAL: ' . Role::count() . PHP_EOL;
