<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('mobile', '09100000000')->first();
if ($user) {
    $user->password = Hash::make('adminadmin');
    $user->save();
    echo 'updated:' . $user->id . PHP_EOL;
} else {
    echo 'not-found' . PHP_EOL;
}
