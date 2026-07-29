<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$users = User::all();
echo "Total users: " . $users->count() . "\n\n";
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->full_name}, Mobile: {$u->mobile}\n";
}
