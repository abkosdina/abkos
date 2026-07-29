<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

$user = User::where('mobile', '09134576502')->first();
if (! $user) {
    $user = new User();
    $user->name = 'Admin User';
    $user->mobile = '09134576502';
    $user->email = '09134576502@example.com';
    $user->password = Hash::make('admin');
    $user->save();
}

$role = Role::firstOrCreate(['name' => 'Super Admin']);
$user->assignRole($role);

echo "created:" . $user->id . PHP_EOL;
echo "mobile:" . $user->mobile . PHP_EOL;
echo "password:admin" . PHP_EOL;
