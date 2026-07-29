<?php

namespace Modules\Negotiation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Negotiation\Actions\CancelNegotiationAction;
use Modules\Negotiation\Actions\CreateNegotiationAction;
use Modules\Negotiation\Actions\CreateOfferAction;
use Modules\Negotiation\Actions\AcceptOfferAction;
use Modules\Negotiation\Actions\CounterOfferAction;
use Modules\Negotiation\Actions\RejectOfferAction;
use Modules\Negotiation\DTO\NegotiationDTO;
use Modules\Negotiation\DTO\NegotiationOfferDTO;
use Modules\Negotiation\Http\Requests\AcceptOfferRequest;
use Modules\Negotiation\Http\Requests\CancelNegotiationRequest;
use Modules\Negotiation\Http\Requests\RejectOfferRequest;
use Modules\Negotiation\Http\Requests\StoreNegotiationRequest;
use Modules\Negotiation\Http\Requests\StoreOfferRequest;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Resources\NegotiationDetailResource;
use Modules\Negotiation\Resources\NegotiationOfferResource;
use Modules\Negotiation\Resources\NegotiationResource;
use Modules\Shared\Base\BaseController;

class NegotiationController extends BaseController
{
    public function __construct(
        protected CreateNegotiationAction $createNegotiationAction,
        protected CreateOfferAction $createOfferAction,
        protected AcceptOfferAction $acceptOfferAction,
        protected RejectOfferAction $rejectOfferAction,
        protected CounterOfferAction $counterOfferAction,
        protected CancelNegotiationAction $cancelNegotiationAction,
    ) {
    }

    public function index(): JsonResponse
    {
        $negotiations = Negotiation::query()->with(['offers', 'histories'])->get();

        return response()->json([
            'success' => true,
            'data' => NegotiationResource::collection($negotiations),
            'message' => 'Negotiations retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $negotiation = Negotiation::query()->where('uuid', $uuid)->with(['offers', 'histories'])->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new NegotiationDetailResource($negotiation),
            'message' => 'Negotiation retrieved successfully.',
        ]);
    }

    public function store(StoreNegotiationRequest $request): JsonResponse
    {
        $dto = new NegotiationDTO(
            advertisementId: (int) $request->input('advertisement_id'),
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

    public function storeOffer(StoreOfferRequest $request, string $uuid): JsonResponse
    {
        $offerDto = new NegotiationOfferDTO(
            negotiationId: 0,
            createdBy: (int) $request->user()?->id,
            amount: (float) $request->input('amount'),
            description: $request->input('description'),
            expiresAt: $request->input('expires_at'),
        );

        $offer = $this->createOfferAction->execute($uuid, $offerDto);

        return response()->json([
            'success' => true,
            'data' => new NegotiationOfferResource($offer),
            'message' => 'Offer created successfully.',
        ]);
    }

    public function accept(AcceptOfferRequest $request, string $uuid): JsonResponse
    {
        $offer = $this->acceptOfferAction->execute($uuid, (int) $request->user()?->id);

        return response()->json([
            'success' => true,
            'data' => new NegotiationOfferResource($offer),
            'message' => 'Offer accepted successfully.',
        ]);
    }

    public function reject(RejectOfferRequest $request, string $uuid): JsonResponse
    {
        $offer = $this->rejectOfferAction->execute($uuid, (int) $request->user()?->id);

        return response()->json([
            'success' => true,
            'data' => new NegotiationOfferResource($offer),
            'message' => 'Offer rejected successfully.',
        ]);
    }

    public function counter(StoreOfferRequest $request, string $uuid): JsonResponse
    {
        $offerDto = new NegotiationOfferDTO(
            negotiationId: 0,
            createdBy: (int) $request->user()?->id,
            amount: (float) $request->input('amount'),
            description: $request->input('description'),
            expiresAt: $request->input('expires_at'),
        );

        $offer = $this->counterOfferAction->execute($uuid, $offerDto);

        return response()->json([
            'success' => true,
            'data' => new NegotiationOfferResource($offer),
            'message' => 'Counter offer created successfully.',
        ]);
    }

    public function cancel(CancelNegotiationRequest $request, string $uuid): JsonResponse
    {
        $negotiation = $this->cancelNegotiationAction->execute($uuid, (int) $request->user()?->id);

        return response()->json([
            'success' => true,
            'data' => new NegotiationResource($negotiation),
            'message' => 'Negotiation cancelled successfully.',
        ]);
    }
}
