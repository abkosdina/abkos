<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

$user = User::where('mobile', '09134576502')->first();
if (! $user) {
    echo "USER_NOT_FOUND\n";
    exit(1);
}

$role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
$user->assignRole($role);

echo "OK {$user->id} {$user->mobile}\n";
