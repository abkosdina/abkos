<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::where('email', 'admin@example.com')->orWhere('mobile', 'admin')->first();
if (! $user) {
    echo "NO_ADMIN\n";
    exit(0);
}

echo "ID: {$user->id}\n";
echo "NAME: {$user->name}\n";
echo "EMAIL: {$user->email}\n";
echo "MOBILE: {$user->mobile}\n";
echo "ROLES: " . json_encode($user->getRoleNames()->all()) . "\n";
