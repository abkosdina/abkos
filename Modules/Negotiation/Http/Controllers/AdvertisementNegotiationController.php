<?php

namespace Modules\Negotiation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Advertisements\Models\Advertisement;
use Modules\Negotiation\Actions\CreateNegotiationAction;
use Modules\Negotiation\DTO\NegotiationDTO;
use Modules\Negotiation\Http\Requests\StoreNegotiationRequest;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Resources\NegotiationResource;
use Modules\Shared\Base\BaseController;

class AdvertisementNegotiationController extends BaseController
{
    public function __construct(protected CreateNegotiationAction $createNegotiationAction)
    {
    }

    public function index(string $uuid): JsonResponse
    {
        $advertisement = Advertisement::query()->where('uuid', $uuid)->firstOrFail();
        $negotiations = Negotiation::query()->where('advertisement_id', $advertisement->id)->get();

        return response()->json([
            'success' => true,
            'data' => NegotiationResource::collection($negotiations),
            'message' => 'Advertisement negotiations retrieved successfully.',
        ]);
    }

    public function store(StoreNegotiationRequest $request, string $uuid): JsonResponse
    {
        $advertisement = Advertisement::query()->where('uuid', $uuid)->firstOrFail();
        $dto = new NegotiationDTO(
            advertisementId: $advertisement->id,
            buyerId: (int) $request->user()?->id,
            sellerId: (int) $request->input('seller_id'),
            conversationId: $request->input('conversation_id'),
        );

        $negotiation = $this->createNegotiationAction->execute($dto);

        return response()->json([
            'success' => true,
            'data' => new NegotiationResource($negotiation),
            'message' => 'Negotiation created successfully.',
        ]);
    }
}
