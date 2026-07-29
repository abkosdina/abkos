<?php

namespace Modules\Negotiation\Services;

use Illuminate\Support\Carbon;
use Modules\Negotiation\DTO\NegotiationOfferDTO;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Repositories\Interfaces\NegotiationHistoryRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationOfferRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationRepositoryInterface;
use Modules\Negotiation\Enums\NegotiationOfferStatus;
use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Events\CounterOfferCreated;
use Modules\Negotiation\Events\OfferCreated;
use Modules\Negotiation\Events\OfferRejected;

class NegotiationOfferService
{
    public function __construct(
        protected NegotiationOfferRepositoryInterface $offerRepository,
        protected NegotiationRepositoryInterface $negotiationRepository,
        protected NegotiationHistoryRepositoryInterface $historyRepository,
    ) {
    }

    public function createOffer(Negotiation $negotiation, NegotiationOfferDTO $dto): NegotiationOffer
    {
        $offer = $this->offerRepository->create([
            'negotiation_id' => $negotiation->id,
            'created_by' => $dto->createdBy,
            'amount' => $dto->amount,
            'description' => $dto->description,
            'status' => NegotiationOfferStatus::Pending,
            'expires_at' => $dto->expiresAt ? Carbon::parse($dto->expiresAt) : now()->addDays(config('negotiation.offer_expiration_days', 7)),
        ]);

        $negotiation->fill([
            'status' => NegotiationStatus::WaitingSeller,
        ]);
        $negotiation->save();

        $this->historyRepository->log($negotiation->id, $dto->createdBy, 'offer_created', ['amount' => $dto->amount]);
        event(new OfferCreated($offer));

        return $offer;
    }

    public function acceptOffer(Negotiation $negotiation, NegotiationOffer $offer, int $actorId): NegotiationOffer
    {
        $offer->fill([
            'status' => NegotiationOfferStatus::Accepted,
            'accepted_at' => now(),
        ]);
        $offer->save();

        $this->historyRepository->log($negotiation->id, $actorId, 'offer_accepted', ['offer_id' => $offer->id]);

        return $offer;
    }

    public function rejectOffer(Negotiation $negotiation, NegotiationOffer $offer, int $actorId): NegotiationOffer
    {
        $offer->fill([
            'status' => NegotiationOfferStatus::Rejected,
            'rejected_at' => now(),
        ]);
        $offer->save();

        $negotiation->fill([
            'status' => NegotiationStatus::WaitingBuyer,
        ]);
        $negotiation->save();

        $this->historyRepository->log($negotiation->id, $actorId, 'offer_rejected', ['offer_id' => $offer->id]);
        event(new OfferRejected($offer));

        return $offer;
    }

    public function counterOffer(Negotiation $negotiation, NegotiationOfferDTO $dto): NegotiationOffer
    {
        $offer = $this->offerRepository->create([
            'negotiation_id' => $negotiation->id,
            'created_by' => $dto->createdBy,
            'amount' => $dto->amount,
            'description' => $dto->description,
            'status' => NegotiationOfferStatus::CounterOffer,
            'expires_at' => $dto->expiresAt ? Carbon::parse($dto->expiresAt) : now()->addDays(config('negotiation.offer_expiration_days', 7)),
        ]);

        $negotiation->fill([
            'status' => NegotiationStatus::WaitingBuyer,
        ]);
        $negotiation->save();

        $this->historyRepository->log($negotiation->id, $dto->createdBy, 'counter_offer_created', ['amount' => $dto->amount]);
        event(new CounterOfferCreated($offer));

        return $offer;
    }
}
