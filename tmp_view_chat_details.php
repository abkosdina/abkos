<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Chat\Models\ChatRoom;
use Modules\Chat\Models\ChatParticipant;
use Modules\Chat\Models\ChatMessage;
use App\Models\User;

$room = ChatRoom::where('name', 'پشتیبانی')->first();
if (!$room) {
    echo "Room not found!\n";
    exit(1);
}

echo "════════════════════════════════════════════════════════════\n";
echo "CHAT ROOM INFORMATION\n";
echo "════════════════════════════════════════════════════════════\n";
echo "Room Name: " . $room->name . "\n";
echo "Room ID: " . $room->id . "\n";
echo "Room Type: " . $room->room_type . "\n";
echo "Status: " . $room->status . "\n";
echo "Created At: " . $room->created_at . "\n\n";

echo "PARTICIPANTS:\n";
echo "────────────────────────────────────────────────────────────\n";
$participants = ChatParticipant::where('chat_room_id', $room->id)->get();
foreach ($participants as $p) {
    $u = User::find($p->user_id);
    $mobile = $u ? $u->mobile : 'N/A';
    echo "  - " . $mobile . " - Role: " . $p->role . " (Joined: " . $p->joined_at . ")\n";
}

echo "\nMESSAGES:\n";
echo "────────────────────────────────────────────────────────────\n";
$messages = ChatMessage::where('chat_room_id', $room->id)->orderBy('id')->get();
foreach ($messages as $m) {
    $sender = User::find($m->sender_id);
    $mobile = $sender ? $sender->mobile : 'N/A';
    echo "  [" . $m->id . "] " . $mobile . ": " . $m->message . "\n";
    echo "       Status: " . $m->status . " | Type: " . $m->message_type . " | Read: " . ($m->read_at ? $m->read_at : 'No') . "\n";
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "Total: " . $participants->count() . " participants, " . $messages->count() . " messages\n";
echo "════════════════════════════════════════════════════════════\n";
