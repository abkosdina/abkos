<?php

namespace Modules\Negotiation\Repositories\Interfaces;

use Modules\Negotiation\Models\Negotiation;

interface NegotiationRepositoryInterface
{
    public function create(array $data): Negotiation;

    public function findByUuid(string $uuid): ?Negotiation;

    public function findById(int $id): ?Negotiation;

    public function update(Negotiation $negotiation, array $data): Negotiation;

    public function getForAdvertisement(int $advertisementId): \Illuminate\Database\Eloquent\Collection;
}
