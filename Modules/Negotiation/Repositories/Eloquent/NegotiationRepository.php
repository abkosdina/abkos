<?php

namespace Modules\Negotiation\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Repositories\Interfaces\NegotiationRepositoryInterface;

class NegotiationRepository implements NegotiationRepositoryInterface
{
    public function create(array $data): Negotiation
    {
        return Negotiation::query()->create($data);
    }

    public function findByUuid(string $uuid): ?Negotiation
    {
        return Negotiation::query()->where('uuid', $uuid)->first();
    }

    public function findById(int $id): ?Negotiation
    {
        return Negotiation::query()->find($id);
    }

    public function update(Negotiation $negotiation, array $data): Negotiation
    {
        $negotiation->fill($data);
        $negotiation->save();

        return $negotiation;
    }

    public function getForAdvertisement(int $advertisementId): Collection
    {
        return Negotiation::query()->where('advertisement_id', $advertisementId)->get();
    }
}
