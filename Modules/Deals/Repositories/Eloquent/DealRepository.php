<?php

namespace Modules\Deals\Repositories\Eloquent;

use Modules\Deals\Models\Deal;
use Modules\Deals\Enums\DealStatus;
use Modules\Deals\Repositories\Interfaces\DealRepositoryInterface;

class DealRepository implements DealRepositoryInterface
{
    public function create(array $data): Deal
    {
        return Deal::query()->create($data);
    }

    public function findById(int $id): ?Deal
    {
        return Deal::query()->find($id);
    }

    public function findByUuid(string $uuid): ?Deal
    {
        return Deal::query()->where('uuid', $uuid)->first();
    }

    public function update(Deal $deal, array $data): Deal
    {
        $deal->fill($data);
        $deal->save();

        return $deal;
    }

    public function findByNegotiationId(int $negotiationId): ?Deal
    {
        return Deal::query()->where('negotiation_id', $negotiationId)->first();
    }

    public function existsActiveDealForNegotiation(int $negotiationId): bool
    {
        return Deal::query()
            ->where('negotiation_id', $negotiationId)
            ->whereIn('status', [DealStatus::Pending->value, DealStatus::Confirmed->value])
            ->exists();
    }

    public function existsActiveDealForAdvertisement(int $advertisementId): bool
    {
        return Deal::query()
            ->where('advertisement_id', $advertisementId)
            ->whereIn('status', [DealStatus::Pending->value, DealStatus::Confirmed->value])
            ->exists();
    }
}
