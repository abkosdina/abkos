<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::take(20)->get();
echo 'COUNT: ' . App\Models\User::count() . PHP_EOL;
foreach ($users as $user) {
    echo $user->id . ' | ' . $user->name . ' | ' . $user->email . ' | ' . $user->mobile . PHP_EOL;
}
