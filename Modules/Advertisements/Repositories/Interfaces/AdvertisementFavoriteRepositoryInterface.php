<?php

namespace Modules\Advertisements\Repositories\Interfaces;

interface AdvertisementFavoriteRepositoryInterface
{
    public function favorite(int $userId, string $advertisementUuid): bool;

    public function unfavorite(int $userId, string $advertisementUuid): bool;

    public function listForUser(int $userId, int $perPage = 20);
}
