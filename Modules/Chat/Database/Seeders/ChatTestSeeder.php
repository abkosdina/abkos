<?php

namespace Modules\Chat\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Chat\Models\ChatMessage;
use Modules\Chat\Models\ChatParticipant;
use Modules\Chat\Models\ChatRoom;
use Illuminate\Support\Str;

class ChatTestSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test2@example.com'],
            [
                'name' => 'Test User 2',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $otherUser = User::firstOrCreate(
            ['email' => 'sample2.user@example.com'],
            [
                'name' => 'Sample User 2',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Site Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $room1 = ChatRoom::firstOrCreate(
            ['name' => 'تست - ادمین و کاربر 2'],
            [
                'uuid' => (string) Str::uuid(),
                'room_type' => 'direct',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        ChatParticipant::firstOrCreate([
            'chat_room_id' => $room1->id,
            'user_id' => $admin->id,
        ], [
            'uuid' => (string) Str::uuid(),
            'role' => 'owner',
            'joined_at' => now(),
            'created_by' => $admin->id,
        ]);

        ChatParticipant::firstOrCreate([
            'chat_room_id' => $room1->id,
            'user_id' => $user->id,
        ], [
            'uuid' => (string) Str::uuid(),
            'role' => 'member',
            'joined_at' => now(),
            'created_by' => $admin->id,
        ]);

        ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room1->id,
            'sender_id' => $user->id,
            'message' => 'سلام ادمین، این چت تستی است.',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $user->id,
        ]);

        ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room1->id,
            'sender_id' => $admin->id,
            'message' => 'سلام، این ادمین است. خوش آمدید.',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $admin->id,
        ]);

        // Room with file exchange
        $room2 = ChatRoom::firstOrCreate(
            ['name' => 'تست-فایل-ارسال'],
            [
                'uuid' => (string) Str::uuid(),
                'room_type' => 'direct',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        ChatParticipant::firstOrCreate([
            'chat_room_id' => $room2->id,
            'user_id' => $admin->id,
        ], [
            'uuid' => (string) Str::uuid(),
            'role' => 'owner',
            'joined_at' => now(),
            'created_by' => $admin->id,
        ]);

        ChatParticipant::firstOrCreate([
            'chat_room_id' => $room2->id,
            'user_id' => $otherUser->id,
        ], [
            'uuid' => (string) Str::uuid(),
            'role' => 'member',
            'joined_at' => now(),
            'created_by' => $admin->id,
        ]);

        $msg = ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room2->id,
            'sender_id' => $otherUser->id,
            'message' => 'من یک عکس نمونه فرستادم',
            'message_type' => 'file',
            'status' => 'sent',
            'created_by' => $otherUser->id,
        ]);

        \Modules\Chat\Models\ChatAttachment::firstOrCreate([
            'chat_message_id' => $msg->id,
            'file_path' => 'cdn/logo.svg',
        ], [
            'uuid' => (string) Str::uuid(),
            'mime_type' => 'image/svg+xml',
            'size_bytes' => 1234,
            'created_by' => $otherUser->id,
        ]);

        ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room2->id,
            'sender_id' => $admin->id,
            'message' => 'تصویر را دریافت کردم، ممنون',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $admin->id,
        ]);
    }
}
