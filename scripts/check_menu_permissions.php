<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$perms = Permission::where('name', 'like', 'menu.%')->get()->pluck('name')->toArray();
echo "PERMISSIONS:\n" . json_encode($perms, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

$rolesToCheck = ['Super Admin', 'Admin', 'Operator', 'Finance', 'User'];
foreach ($rolesToCheck as $r) {
    $role = Role::where('name', $r)->first();
    $names = $role ? $role->getPermissionNames()->toArray() : [];
    echo "ROLE: {$r}\n" . json_encode($names, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
}
