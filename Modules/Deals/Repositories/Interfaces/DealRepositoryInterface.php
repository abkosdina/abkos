<?php

namespace Modules\Deals\Repositories\Interfaces;

use Modules\Deals\Models\Deal;

interface DealRepositoryInterface
{
    public function create(array $data): Deal;

    public function findById(int $id): ?Deal;

    public function findByUuid(string $uuid): ?Deal;

    public function update(Deal $deal, array $data): Deal;

    public function findByNegotiationId(int $negotiationId): ?Deal;

    public function existsActiveDealForNegotiation(int $negotiationId): bool;

    public function existsActiveDealForAdvertisement(int $advertisementId): bool;
}
