<?php

namespace Modules\Chat\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Chat\Http\Requests\AddChatMessageAttachmentRequest;
use Modules\Shared\Base\BaseController;
use Modules\Chat\Requests\CreateChatRoomRequest;
use Modules\Chat\Requests\SendChatMessageRequest;
use Modules\Chat\Interfaces\ChatServiceInterface;

class ChatController extends BaseController
{
    public function __construct(protected ChatServiceInterface $chatService)
    {
    }

    public function indexRooms(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->chatService->listRooms(request()->user()->id),
            'message' => 'Chat rooms loaded successfully.',
        ]);
    }

    public function storeRoom(CreateChatRoomRequest $request): JsonResponse
    {
        $room = $this->chatService->createRoom(request()->user()->id, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $room,
            'message' => 'Chat room created successfully.',
        ], 201);
    }

    public function showRoom($room): JsonResponse
    {
        $roomObject = $this->chatService->getRoom($room);

        return response()->json([
            'success' => true,
            'data' => $roomObject,
            'message' => 'Chat room loaded successfully.',
        ]);
    }

    public function listMessages($room): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->chatService->listMessages($room),
            'message' => 'Chat messages loaded successfully.',
        ]);
    }

    public function sendMessage(SendChatMessageRequest $request, $room): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachments'] = [$request->file('attachment')];
        }

        $message = $this->chatService->sendMessage(request()->user()->id, $room, $data);

        return response()->json([
            'success' => true,
            'data' => $message,
            'message' => 'Message sent successfully.',
        ], 201);
    }

    public function markRoomRead($room): JsonResponse
    {
        $result = $this->chatService->markRoomRead($room, request()->user()->id);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Room marked read successfully.',
        ]);
    }

    public function indexArchivedRooms(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->chatService->listArchivedRooms(request()->user()->id),
            'message' => 'Archived chat rooms loaded successfully.',
        ]);
    }

    public function archiveRoom($room): JsonResponse
    {
        $roomObject = $this->chatService->archiveRoom($room, request()->user()->id);

        return response()->json([
            'success' => true,
            'data' => $roomObject,
            'message' => 'Chat room archived successfully.',
        ]);
    }

    public function restoreRoom($room): JsonResponse
    {
        $roomObject = $this->chatService->restoreRoom($room, request()->user()->id);

        return response()->json([
            'success' => true,
            'data' => $roomObject,
            'message' => 'Chat room restored successfully.',
        ]);
    }

    public function addAttachment(AddChatMessageAttachmentRequest $request, $message): JsonResponse
    {
        $attachment = $this->chatService->addAttachment($message, request()->user()->id, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $attachment,
            'message' => 'Attachment uploaded successfully.',
        ], 201);
    }
}
