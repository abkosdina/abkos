<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Update admin name
$admin = User::find(1);
if ($admin) {
    $admin->name = 'خانم گراوند';
    $admin->save();
    echo "✅ Admin name updated to: " . $admin->name . "\n";
}

// Update user name
$user = User::find(9);
if ($user) {
    $user->name = 'مجتبی غریب';
    $user->save();
    echo "✅ User name updated to: " . $user->name . "\n";
}
