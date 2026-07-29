<?php

namespace Modules\Negotiation\Repositories\Interfaces;

use Modules\Negotiation\Models\NegotiationOffer;

interface NegotiationOfferRepositoryInterface
{
    public function create(array $data): NegotiationOffer;

    public function findByUuid(string $uuid): ?NegotiationOffer;

    public function update(NegotiationOffer $offer, array $data): NegotiationOffer;

    public function getPendingOffers(int $negotiationId): \Illuminate\Database\Eloquent\Collection;
}
