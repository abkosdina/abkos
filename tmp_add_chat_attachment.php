<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Chat\Models\ChatRoom;
use Modules\Chat\Models\ChatMessage;
use Modules\Chat\Models\ChatAttachment;
use App\Models\User;

$room = ChatRoom::where('name', 'پشتیبانی')->first();
$admin = User::find(1);

// Create a message with attachment
$message = ChatMessage::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'chat_room_id' => $room->id,
    'sender_id' => $admin->id,
    'message' => 'در اینجا فایل درخواستی شما است.',
    'message_type' => 'text',
    'status' => 'sent',
    'read_at' => now()->addMinutes(6),
    'created_by' => $admin->id,
]);

// Add attachment to message
ChatAttachment::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'chat_message_id' => $message->id,
    'file_path' => 'chat/support/document_' . now()->timestamp . '.pdf',
    'mime_type' => 'application/pdf',
    'size_bytes' => 512000,
    'created_by' => $admin->id,
]);

echo "✅ Message with attachment created!\n";
echo "   Message ID: {$message->id}\n";
echo "   File: chat/support/document_" . now()->timestamp . ".pdf\n";
echo "   Size: 512KB\n";
