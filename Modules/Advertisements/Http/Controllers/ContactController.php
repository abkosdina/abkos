<?php

namespace Modules\Advertisements\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Requests\StoreAdvertisementContactRequest;
use Modules\Advertisements\Services\ContactService;

class ContactController
{
    /**
     * Store a new contact inquiry for an advertisement
     *
     * POST /api/advertisements/{id}/contacts
     */
    public function store(
        Advertisement $advertisement,
        StoreAdvertisementContactRequest $request,
        ContactService $contactService
    ): JsonResponse {
        $validated = $request->validated();

        $success = $contactService->recordContact(
            advertisementId: $advertisement->id,
            userId: $request->user()?->id,
            name: $validated['name'],
            email: $validated['email'],
            phone: $validated['phone'] ?? null,
            message: $validated['message'],
            ip: $request->ip(),
            device: $request->header('User-Agent'),
            sessionId: $request->sessionId ?? null
        );

        if ($success) {
            return response()->json([
                'message' => 'Contact inquiry submitted successfully',
                'data' => [
                    'advertisement_id' => $advertisement->id,
                    'status' => 'pending',
                ],
            ], 201);
        }

        return response()->json([
            'message' => 'Failed to submit contact inquiry',
            'error' => 'An error occurred while processing your request',
        ], 500);
    }
}
