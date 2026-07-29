<?php

namespace Modules\Negotiation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Negotiation\DTO\NegotiationDTO;
use Modules\Negotiation\DTO\NegotiationOfferDTO;
use Modules\Negotiation\Enums\NegotiationOfferStatus;
use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Events\NegotiationCancelled;
use Modules\Negotiation\Events\NegotiationCompleted;
use Modules\Negotiation\Events\NegotiationCreated;
use Modules\Negotiation\Events\NegotiationExpired;
use Modules\Negotiation\Events\OfferAccepted;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Repositories\Interfaces\NegotiationHistoryRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationOfferRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationRepositoryInterface;

class NegotiationService
{
    public function __construct(
        protected NegotiationRepositoryInterface $negotiationRepository,
        protected NegotiationOfferRepositoryInterface $offerRepository,
        protected NegotiationHistoryRepositoryInterface $historyRepository,
        protected NegotiationValidationService $validationService,
        protected NegotiationWorkflowService $workflowService,
        protected NegotiationOfferService $offerService,
    ) {
    }

    public function createNegotiation(NegotiationDTO $dto): Negotiation
    {
        $advertisement = \Modules\Advertisements\Models\Advertisement::query()->findOrFail($dto->advertisementId);
        $this->validationService->validateNegotiationCreation($this->resolveUser($dto->buyerId), $advertisement);

        $negotiation = $this->negotiationRepository->create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'advertisement_id' => $dto->advertisementId,
            'buyer_id' => $dto->buyerId,
            'seller_id' => $dto->sellerId,
            'conversation_id' => $dto->conversationId,
            'status' => NegotiationStatus::Pending,
            'started_at' => now(),
        ]);

        $this->historyRepository->log($negotiation->id, $dto->buyerId, 'negotiation_created', ['advertisement_id' => $dto->advertisementId]);
        event(new NegotiationCreated($negotiation));

        return $negotiation;
    }

    public function createOffer(string|int $negotiationId, NegotiationOfferDTO $dto): NegotiationOffer
    {
        $negotiation = is_int($negotiationId)
            ? $this->negotiationRepository->findById($negotiationId)
            : $this->negotiationRepository->findByUuid($negotiationId);

        if (! $negotiation) {
            throw new \InvalidArgumentException('Negotiation not found.');
        }

        $this->validationService->validateOfferCreation($this->resolveUser($dto->createdBy), $negotiation, $dto->amount);

        return $this->offerService->createOffer($negotiation, $dto);
    }

    public function acceptOffer(string $offerUuid, int $actorId): NegotiationOffer
    {
        $offer = $this->offerRepository->findByUuid($offerUuid);
        if (! $offer) {
            throw new \InvalidArgumentException('Offer not found.');
        }

        $negotiation = $offer->negotiation;
        $this->validationService->validateOfferAcceptance($this->resolveUser($actorId), $negotiation);

        return DB::transaction(function () use ($offer, $negotiation, $actorId): NegotiationOffer {
            $acceptedOffer = $this->offerService->acceptOffer($negotiation, $offer, $actorId);
            $this->workflowService->completeNegotiation($negotiation, $acceptedOffer, $actorId);
            event(new NegotiationCompleted($negotiation, $acceptedOffer));

            return $acceptedOffer;
        });
    }

    public function rejectOffer(string $offerUuid, int $actorId): NegotiationOffer
    {
        $offer = $this->offerRepository->findByUuid($offerUuid);
        if (! $offer) {
            throw new \InvalidArgumentException('Offer not found.');
        }

        $negotiation = $offer->negotiation;
        $offer = $this->offerService->rejectOffer($negotiation, $offer, $actorId);
        $this->historyRepository->log($negotiation->id, $actorId, 'offer_rejected', ['offer_id' => $offer->id]);

        return $offer;
    }

    public function counterOffer(string $offerUuid, NegotiationOfferDTO $dto): NegotiationOffer
    {
        $offer = $this->offerRepository->findByUuid($offerUuid);
        if (! $offer) {
            throw new \InvalidArgumentException('Offer not found.');
        }

        $negotiation = $offer->negotiation;
        return $this->offerService->counterOffer($negotiation, $dto);
    }

    public function cancelNegotiation(string $uuid, int $actorId): Negotiation
    {
        $negotiation = $this->negotiationRepository->findByUuid($uuid);
        if (! $negotiation) {
            throw new \InvalidArgumentException('Negotiation not found.');
        }

        $negotiation->fill([
            'status' => NegotiationStatus::Cancelled,
            'cancelled_at' => now(),
            'closed_at' => now(),
        ]);
        $negotiation->save();

        $this->historyRepository->log($negotiation->id, $actorId, 'negotiation_cancelled', ['uuid' => $uuid]);
        event(new NegotiationCancelled($negotiation));

        return $negotiation;
    }

    public function expireNegotiation(string $uuid, int $actorId): Negotiation
    {
        $negotiation = $this->negotiationRepository->findByUuid($uuid);
        if (! $negotiation) {
            throw new \InvalidArgumentException('Negotiation not found.');
        }

        $negotiation->fill([
            'status' => NegotiationStatus::Expired,
            'expired_at' => now(),
            'closed_at' => now(),
        ]);
        $negotiation->save();

        $this->historyRepository->log($negotiation->id, $actorId, 'negotiation_expired', ['uuid' => $uuid]);
        event(new NegotiationExpired($negotiation));

        return $negotiation;
    }

    protected function resolveUser(int $id): \App\Models\User
    {
        return \App\Models\User::query()->findOrFail($id);
    }
}
