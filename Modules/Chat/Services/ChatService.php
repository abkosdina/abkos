<?php

namespace Modules\Chat\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Chat\Interfaces\ChatServiceInterface;
use Modules\Chat\Repositories\Interfaces\ChatAttachmentRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatMessageReadRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatMessageRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatParticipantRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatRoomRepositoryInterface;
use Modules\Chat\Models\ChatMessage;

class ChatService implements ChatServiceInterface
{
    public function __construct(
        protected ChatRoomRepositoryInterface $roomRepository,
        protected ChatMessageRepositoryInterface $messageRepository,
        protected ChatAttachmentRepositoryInterface $attachmentRepository,
        protected ChatParticipantRepositoryInterface $participantRepository,
        protected ChatMessageReadRepositoryInterface $messageReadRepository,
    ) {
    }

    public function listRooms(int|string $userId): array
    {
        return $this->roomRepository->getForUser($userId);
    }

    public function createRoom(int|string $creatorId, array $data): object
    {
        $room = $this->roomRepository->create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'] ?? null,
            'room_type' => $data['room_type'] ?? 'direct',
            'status' => 'active',
            'created_by' => $creatorId,
        ]);

        $this->participantRepository->create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $room->id,
            'user_id' => $creatorId,
            'role' => 'owner',
            'joined_at' => now(),
            'created_by' => $creatorId,
        ]);

        foreach ($data['participants'] as $userId) {
            if ($userId == $creatorId) {
                continue;
            }

            $this->participantRepository->create([
                'uuid' => (string) Str::uuid(),
                'chat_room_id' => $room->id,
                'user_id' => $userId,
                'role' => 'member',
                'joined_at' => now(),
                'created_by' => $creatorId,
            ]);
        }

        return $room;
    }

    public function getRoom(int|string $roomId): ?object
    {
        return $this->roomRepository->findWithRelations($roomId);
    }

    public function listMessages(int|string $roomId): array
    {
        return $this->messageRepository->getByRoom($roomId);
    }

    public function listArchivedRooms(int|string $userId): array
    {
        return $this->roomRepository->getArchivedForUser($userId);
    }

    public function archiveRoom(int|string $roomId, int|string $userId): object
    {
        $this->roomRepository->update($roomId, [
            'status' => 'archived',
            'updated_by' => $userId,
        ]);

        return $this->roomRepository->find($roomId);
    }

    public function restoreRoom(int|string $roomId, int|string $userId): object
    {
        $this->roomRepository->update($roomId, [
            'status' => 'active',
            'updated_by' => $userId,
        ]);

        return $this->roomRepository->find($roomId);
    }

    public function sendMessage(int|string $senderId, int|string $roomId, array $data): object
    {
        $message = $this->messageRepository->create([
            'uuid' => (string) Str::uuid(),
            'chat_room_id' => $roomId,
            'sender_id' => $senderId,
            'message' => $data['message'] ?? null,
            'message_type' => $data['message_type'] ?? 'text',
            'status' => 'sent',
            'created_by' => $senderId,
        ]);

        if (! empty($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                if ($attachment instanceof UploadedFile) {
                    $attachment = $this->storeUploadedAttachment($attachment, $senderId);
                }

                $this->attachmentRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'chat_message_id' => $message->id,
                    'file_path' => $attachment['file_path'],
                    'mime_type' => $attachment['mime_type'] ?? null,
                    'size_bytes' => $attachment['size_bytes'] ?? null,
                    'created_by' => $senderId,
                ]);
            }
        }

        return $message->load('attachments');
    }

    public function addAttachment(int|string $messageId, int|string $userId, array $data): object
    {
        $message = $this->messageRepository->find($messageId);

        if (! $message) {
            throw new \RuntimeException('Message not found.');
        }

        $attachmentData = $data['attachment'];

        if ($attachmentData instanceof UploadedFile) {
            $attachmentData = $this->storeUploadedAttachment($attachmentData, $userId);
        }

        return $this->attachmentRepository->create([
            'uuid' => (string) Str::uuid(),
            'chat_message_id' => $message->id,
            'file_path' => $attachmentData['file_path'],
            'mime_type' => $attachmentData['mime_type'] ?? null,
            'size_bytes' => $attachmentData['size_bytes'] ?? null,
            'created_by' => $userId,
        ]);
    }

    protected function storeUploadedAttachment(UploadedFile $file, int|string $userId): array
    {
        $filePath = $file->store('chat/attachments', config('chat.attachment_disk'));

        return [
            'file_path' => $filePath,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'created_by' => $userId,
        ];
    }

    public function markRoomRead(int|string $roomId, int|string $userId): array
    {
        $messages = $this->messageRepository->getByRoom($roomId);

        foreach ($messages as $message) {
            $this->messageReadRepository->markAsRead($message->id, $userId);
        }

        return $messages;
    }
}
