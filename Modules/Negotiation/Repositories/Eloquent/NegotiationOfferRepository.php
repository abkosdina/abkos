<?php

namespace Modules\Negotiation\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Repositories\Interfaces\NegotiationOfferRepositoryInterface;

class NegotiationOfferRepository implements NegotiationOfferRepositoryInterface
{
    public function create(array $data): NegotiationOffer
    {
        return NegotiationOffer::query()->create($data);
    }

    public function findByUuid(string $uuid): ?NegotiationOffer
    {
        return NegotiationOffer::query()->where('uuid', $uuid)->first();
    }

    public function update(NegotiationOffer $offer, array $data): NegotiationOffer
    {
        $offer->fill($data);
        $offer->save();

        return $offer;
    }

    public function getPendingOffers(int $negotiationId): Collection
    {
        return NegotiationOffer::query()
            ->where('negotiation_id', $negotiationId)
            ->whereIn('status', ['Pending', 'CounterOffer'])
            ->get();
    }
}
