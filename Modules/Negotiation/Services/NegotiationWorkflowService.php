<?php

namespace Modules\Negotiation\Services;

use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Repositories\Interfaces\NegotiationHistoryRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationOfferRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationRepositoryInterface;
use Modules\Negotiation\Services\NegotiationStateService;

class NegotiationWorkflowService
{
    public function __construct(
        protected NegotiationRepositoryInterface $negotiationRepository,
        protected NegotiationOfferRepositoryInterface $offerRepository,
        protected NegotiationHistoryRepositoryInterface $historyRepository,
        protected NegotiationStateService $stateService,
    ) {
    }

    public function completeNegotiation(Negotiation $negotiation, NegotiationOffer $offer, int $actorId): void
    {
        if ($negotiation->status !== NegotiationStatus::Accepted) {
            $negotiation = $this->stateService->transition($negotiation, NegotiationStatus::Accepted);
        }

        if ($negotiation->status !== NegotiationStatus::Accepted) {
            $negotiation->status = NegotiationStatus::Accepted;
        }

        if (! $negotiation->accepted_at) {
            $negotiation->accepted_at = now();
        }

        if (! $negotiation->closed_at) {
            $negotiation->closed_at = now();
        }

        $negotiation->selected_offer_id = $offer->id;
        $negotiation->agreed_price = $offer->amount;
        $negotiation->save();

        $this->historyRepository->log($negotiation->id, $actorId, 'negotiation_completed', ['offer_id' => $offer->id]);
    }
}
