<?php

namespace Modules\Chat\Interfaces;

interface ChatServiceInterface
{
    public function listRooms(int|string $userId): array;

    public function listArchivedRooms(int|string $userId): array;

    public function createRoom(int|string $creatorId, array $data): object;

    public function getRoom(int|string $roomId): ?object;

    public function listMessages(int|string $roomId): array;

    public function sendMessage(int|string $senderId, int|string $roomId, array $data): object;

    public function addAttachment(int|string $messageId, int|string $userId, array $data): object;

    public function markRoomRead(int|string $roomId, int|string $userId): array;

    public function archiveRoom(int|string $roomId, int|string $userId): object;

    public function restoreRoom(int|string $roomId, int|string $userId): object;
}
