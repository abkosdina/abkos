<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

$user = User::where('mobile', '09134576502')->first();
if (! $user) {
    echo "USER NOT FOUND\n";
    exit(1);
}

$roleIds = DB::table('model_has_roles')->where('model_id', $user->id)->pluck('role_id')->toArray();
$roleNames = DB::table('roles')->whereIn('id', $roleIds)->pluck('name')->toArray();
echo 'User roles: ' . implode(',', $roleNames) . '\n';

$permissionIds = DB::table('role_has_permissions')->whereIn('role_id', $roleIds)->pluck('permission_id')->unique()->toArray();
$permissionNames = DB::table('permissions')->whereIn('id', $permissionIds)->pluck('name')->toArray();
echo 'Role perms: ' . implode(',', $permissionNames) . '\n';

$directPermissionIds = DB::table('model_has_permissions')->where('model_id', $user->id)->pluck('permission_id')->toArray();
directPermissionIds = array_unique($directPermissionIds);
dd($directPermissionIds);
$directPermissionNames = DB::table('permissions')->whereIn('id', $directPermissionIds)->pluck('name')->toArray();
echo 'Direct perms: ' . implode(',', $directPermissionNames) . '\n';
