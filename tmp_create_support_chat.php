<?php
/**
 * Script to create a support chat room between admin and user 09134576502
 * Usage: php tmp_create_support_chat.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Chat\Models\ChatRoom;
use Modules\Chat\Models\ChatParticipant;
use Modules\Chat\Models\ChatMessage;
use App\Models\User;

// Find user by mobile
$user = User::where('mobile', '09134576502')->first();
if (!$user) {
    echo "❌ User with mobile 09134576502 not found!\n";
    exit(1);
}
echo "✅ User found: {$user->full_name} (ID: {$user->id})\n";

// Find admin (use first user or specific ID)
$admin = User::find(1);
if (!$admin) {
    $admin = User::first();
}

if (!$admin) {
    echo "❌ Admin user not found!\n";
    exit(1);
}
echo "✅ Admin found: {$admin->full_name} (ID: {$admin->id})\n";

// Create ChatRoom
$chatRoom = ChatRoom::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'name' => 'پشتیبانی',
    'room_type' => 'support',
    'status' => 'active',
    'created_by' => $admin->id,
]);
echo "✅ Chat room created: {$chatRoom->name} (ID: {$chatRoom->id})\n";

// Add participants
ChatParticipant::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'chat_room_id' => $chatRoom->id,
    'user_id' => $admin->id,
    'role' => 'admin',
    'joined_at' => now(),
    'created_by' => $admin->id,
]);

ChatParticipant::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'chat_room_id' => $chatRoom->id,
    'user_id' => $user->id,
    'role' => 'member',
    'joined_at' => now(),
    'created_by' => $admin->id,
]);
echo "✅ Participants added\n";

// Create test messages
$messages = [
    [
        'sender_id' => $admin->id,
        'message' => 'سلام! خوش آمدید. چطور می‌تونم کمکتون کنم؟',
    ],
    [
        'sender_id' => $user->id,
        'message' => 'سلام، من یک سوال درباره حسابم دارم.',
    ],
    [
        'sender_id' => $admin->id,
        'message' => 'بفرمایید، چی می‌تونم برای شما انجام بدم؟',
    ],
    [
        'sender_id' => $user->id,
        'message' => 'لطفاً یک فایل برای من بفرستید.',
    ],
    [
        'sender_id' => $admin->id,
        'message' => 'بسیار خوب، منتظر باشید...',
    ],
];

foreach ($messages as $index => $msgData) {
    ChatMessage::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'chat_room_id' => $chatRoom->id,
        'sender_id' => $msgData['sender_id'],
        'message' => $msgData['message'],
        'message_type' => 'text',
        'status' => 'sent',
        'read_at' => now()->addMinutes($index),
        'created_by' => $msgData['sender_id'],
    ]);
}
echo "✅ 5 test messages created\n";

echo "\n✨ Chat room 'پشتیبانی' successfully created!\n";
echo "   - Admin: {$admin->full_name}\n";
echo "   - User: {$user->full_name}\n";
echo "   - Messages: 5\n";
