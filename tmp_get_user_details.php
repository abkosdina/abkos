<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Modules\Chat\Models\ChatRoom;

// Get admin user
$admin = User::find(1);
echo "ADMIN USER (ID: 1):\n";
echo "─────────────────────────────────────────\n";
if ($admin) {
    echo "Name: " . $admin->name . "\n";
    echo "Mobile: " . $admin->mobile . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Profile Photo: " . ($admin->profile_photo_path ?? 'no photo') . "\n";
    echo "Role: admin\n";
}
echo "\n";

// Get regular user
$user = User::find(9);
echo "REGULAR USER (ID: 9):\n";
echo "─────────────────────────────────────────\n";
if ($user) {
    echo "Name: " . $user->name . "\n";
    echo "Mobile: " . $user->mobile . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Profile Photo: " . ($user->profile_photo_path ?? 'no photo') . "\n";
    echo "Is Verified: " . ($user->is_verified ?? false ? 'Yes' : 'No') . "\n";
    echo "Is VIP: " . ($user->is_vip ?? false ? 'Yes' : 'No') . "\n";
}
echo "\n";

// Get chat room with messages
$room = ChatRoom::with('participants.user', 'messages.sender', 'messages.attachments')
    ->where('name', 'پشتیبانی')
    ->first();

if ($room) {
    echo "CHAT ROOM:\n";
    echo "─────────────────────────────────────────\n";
    echo "Name: " . $room->name . "\n";
    echo "Type: " . $room->type . "\n";
    echo "Status: " . $room->status . "\n";
    echo "Messages Count: " . $room->messages->count() . "\n";
}
