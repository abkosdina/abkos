<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

$perm = Permission::where('name', 'menu.users')->first();
if ($perm) {
    echo 'PERM: ' . $perm->name . ' display=' . $perm->display_name . ' slug_fa=' . $perm->slug_fa . '\n';
} else {
    echo 'PERM MISSING\n';
}
$role = Role::where('name', 'Super Admin')->first();
if ($role) {
    echo 'ROLE: ' . $role->name . ' perms=' . implode(',', $role->permissions->pluck('name')->toArray()) . '\n';
}
$user = User::where('mobile', '09134576502')->first();
if ($user) {
    echo 'USER roles=' . implode(',', $user->getRoleNames()->toArray()) . '\n';
    echo 'USER perms=' . implode(',', $user->getPermissionNames()->toArray()) . '\n';
}
