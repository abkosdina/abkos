<?php

namespace Modules\Chat\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Modules\Chat\Models\ChatRoom;
use Modules\Chat\Models\ChatMessage;

class ChatModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_chat_room()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/chat/rooms', [
            'name' => 'Test Room',
            'participants' => [$otherUser->id],
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('chat_rooms', ['name' => 'Test Room']);
        $this->assertDatabaseHas('chat_participants', ['user_id' => $user->id]);
        $this->assertDatabaseHas('chat_participants', ['user_id' => $otherUser->id]);
    }

    public function test_authenticated_user_can_list_chat_rooms()
    {
        $user = User::factory()->create();
        $room = ChatRoom::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Room A',
            'room_type' => 'direct',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/chat/rooms');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_authenticated_user_can_send_message()
    {
        $user = User::factory()->create();
        $room = ChatRoom::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Room B',
            'room_type' => 'direct',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/chat/rooms/{$room->id}/messages", [
            'message' => 'Hello world',
            'message_type' => 'text',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('chat_messages', ['chat_room_id' => $room->id, 'sender_id' => $user->id]);
    }

    public function test_authenticated_user_can_list_messages_for_room()
    {
        $user = User::factory()->create();
        $room = ChatRoom::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Room C',
            'room_type' => 'direct',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        ChatMessage::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'chat_room_id' => $room->id,
            'sender_id' => $user->id,
            'message' => 'Message one',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/chat/rooms/{$room->id}/messages");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(1, 'data');
    }

    public function test_authenticated_user_can_mark_room_messages_as_read()
    {
        $user = User::factory()->create();
        $room = ChatRoom::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Room D',
            'room_type' => 'direct',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $message = ChatMessage::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'chat_room_id' => $room->id,
            'sender_id' => $user->id,
            'message' => 'Read this message',
            'message_type' => 'text',
            'status' => 'sent',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/chat/rooms/{$room->id}/mark-read");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('chat_message_reads', ['chat_message_id' => $message->id, 'user_id' => $user->id]);
    }

    public function test_authenticated_user_can_view_room_details()
    {
        $user = User::factory()->create();
        $room = ChatRoom::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Room Detail',
            'room_type' => 'direct',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/chat/rooms/{$room->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['id' => $room->id]);
    }

    public function test_authenticated_user_can_send_message_with_attachment()
    {
        Storage::fake(config('chat.attachment_disk'));

        $user = User::factory()->create();
        $room = ChatRoom::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Room Attachment',
            'room_type' => 'direct',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')->post(
            "/api/v1/chat/rooms/{$room->id}/messages",
            ['message' => 'Here is a file', 'message_type' => 'file', 'attachment' => $file],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('chat_messages', ['chat_room_id' => $room->id, 'sender_id' => $user->id, 'message_type' => 'file']);
        $this->assertDatabaseHas('chat_attachments', ['created_by' => $user->id]);

        $attachment = \Modules\Chat\Models\ChatAttachment::first();
        Storage::disk(config('chat.attachment_disk'))->assertExists($attachment->file_path);
    }

    public function test_guest_cannot_access_chat_routes()
    {
        $this->getJson('/api/v1/chat/rooms')->assertStatus(401);
    }
}
