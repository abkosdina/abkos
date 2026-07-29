<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Chat\Models\ChatRoom;
use Modules\Chat\Models\ChatMessage;
use App\Models\User;

class ChatRoomController extends Controller
{
    public function show($roomId)
    {
        $chatRoom = ChatRoom::with('messages.sender', 'messages.attachments', 'participants.user')
            ->findOrFail($roomId);

        $messages = $chatRoom->messages()
            ->with('sender', 'attachments')
            ->orderBy('created_at', 'asc')
            ->get();

        $admin = User::find(1);
        $user = User::find(9);

        return view('chat::chat-room', [
            'chatRoom' => $chatRoom,
            'messages' => $messages,
            'admin' => $admin,
            'user' => $user,
        ]);
    }

    public function getNewMessages($roomId)
    {
        $since = request()->query('since');
        $query = ChatMessage::where('chat_room_id', $roomId)
            ->with('sender', 'attachments');

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->json([
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'sender_photo' => $message->sender->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->name),
                    'message' => $message->message,
                    'status' => $message->status,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'created_at_fa' => $message->created_at->locale('fa')->format('H:i'),
                    'attachments' => $message->attachments->map(function ($att) {
                        return [
                            'id' => $att->id,
                            'name' => $att->original_name,
                            'size' => number_format($att->size_bytes / 1024, 2),
                            'mime_type' => $att->mime_type,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'last_message_time' => $messages->isNotEmpty() 
                ? $messages->last()->created_at->format('Y-m-d H:i:s')
                : $since,
        ]);
    }
}
