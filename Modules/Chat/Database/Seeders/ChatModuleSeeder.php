<?php

namespace Modules\Chat\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Chat\Models\ChatMessage;
use Modules\Chat\Models\ChatParticipant;
use Modules\Chat\Models\ChatRoom;
use Illuminate\Support\Str;

class ChatModuleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $otherUser = User::firstOrCreate(
            ['email' => 'sample.user@example.com'],
            [
                'name' => 'Sample User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $room = ChatRoom::firstOrCreate(
            ['name' => 'گفتگوی پشتیبانی'],
            [
                'uuid' => (string) Str::uuid(),
                'room_type' => 'direct',
                'status' => 'active',
                'created_by' => $user->id,
            ]
        );

        ChatParticipant::firstOrCreate([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
        ], [
            'uuid' => (string) Str::uuid(),
            'role' => 'owner',
            'joined_at' => now(),
            'created_by' => $user->id,
        ]);

        ChatParticipant::firstOrCreate([
            'chat_room_id' => $room->id,
            'user_id' => $otherUser->id,
        ], [
            'uuid' => (string) Str::uuid(),
            'role' => 'member',
            'joined_at' => now(),
            'created_by' => $user->id,
        ]);

        ChatMessage::firstOrCreate([
            'chat_room_id' => $room->id,
            'message' => 'سلام، چگونه می‌توانیم به شما کمک کنیم؟',
        ], [
            'uuid' => (string) Str::uuid(),
            'sender_id' => $otherUser->id,
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $otherUser->id,
        ]);

        $archivedRoom = ChatRoom::firstOrCreate(
            ['name' => 'گفتگوی آرشیو شده'],
            [
                'uuid' => (string) Str::uuid(),
                'room_type' => 'direct',
                'status' => 'archived',
                'created_by' => $user->id,
            ]
        );

        ChatParticipant::firstOrCreate([
            'chat_room_id' => $archivedRoom->id,
            'user_id' => $user->id,
        ], [
            'uuid' => (string) Str::uuid(),
            'role' => 'owner',
            'joined_at' => now(),
            'created_by' => $user->id,
        ]);

        ChatMessage::firstOrCreate([
            'chat_room_id' => $archivedRoom->id,
            'message' => 'این گفتگو به آرشیو منتقل شده است.',
        ], [
            'uuid' => (string) Str::uuid(),
            'sender_id' => $user->id,
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $user->id,
        ]);

        // Create an admin user and additional test conversations including file attachments
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Site Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Conversation 1: admin <-> test user
        $room1 = ChatRoom::firstOrCreate(
            ['name' => 'کاربر - ادمین (نمونه)'],
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

        $m1 = ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room1->id,
            'sender_id' => $user->id,
            'message' => 'سلام، این یک پیام تستی است. آیا شما ادمین هستید؟',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $user->id,
        ]);

        $m2 = ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room1->id,
            'sender_id' => $admin->id,
            'message' => 'بله، من ادمین هستم. چگونه کمک کنم؟',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $admin->id,
        ]);

        // Conversation 2: include an image/file attachment exchanged
        $room2 = ChatRoom::firstOrCreate(
            ['name' => 'پشتیبانی - نمونه تصویر'],
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

        $msgFileUser = ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room2->id,
            'sender_id' => $otherUser->id,
            'message' => 'این عکس نمونه را ارسال کردم',
            'message_type' => 'file',
            'status' => 'sent',
            'created_by' => $otherUser->id,
        ]);

        // create attachment record pointing to an existing public asset (logo.svg)
        \Modules\Chat\Models\ChatAttachment::firstOrCreate([
            'chat_message_id' => $msgFileUser->id,
            'file_path' => 'cdn/logo.svg',
        ], [
            'uuid' => (string) Str::uuid(),
            'mime_type' => 'image/svg+xml',
            'size_bytes' => 1234,
            'created_by' => $otherUser->id,
        ]);

        $msgFileAdmin = ChatMessage::create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room2->id,
            'sender_id' => $admin->id,
            'message' => 'با تشکر، تصویر را دریافت کردم',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $admin->id,
        ]);
    }
}
