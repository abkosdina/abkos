<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Chat\Models\ChatMessage;

// Get the room ID
$roomId = 1;

// Add a new test message from admin
$message = ChatMessage::create([
    'chat_room_id' => $roomId,
    'sender_id' => 1,
    'message' => 'سلام! این پیام جدید است و بدون رفرش صفحه نمایش داده می‌شود! 🚀',
    'status' => 'sent',
    'created_by' => 1,
]);

echo "✅ New message created! (ID: {$message->id})\n";
echo "📨 Message: {$message->message}\n";
echo "👤 Sender ID: {$message->sender_id}\n";
echo "🕐 Created: {$message->created_at}\n";
echo "\n💡 اگر این صفحه چت رو باز دارید، پیام جدید بدون رفرش نمایش داده می‌شود!\n";
