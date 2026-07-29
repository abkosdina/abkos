<?php

namespace Modules\Advertisements\Services;

use Modules\Advertisements\DTO\FavoriteDTO;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementFavoriteRepositoryInterface;
use Modules\Advertisements\Events\AdvertisementFavorited;
use Illuminate\Support\Facades\Event;

class FavoriteService
{
    public function __construct(protected AdvertisementFavoriteRepositoryInterface $repo)
    {
    }

    public function favorite(int $userId, string $advertisementUuid): bool
    {
        $res = $this->repo->favorite($userId, $advertisementUuid);
        if ($res) {
            Event::dispatch(new AdvertisementFavorited($userId, $advertisementUuid));
        }

        return (bool) $res;
    }

    public function unfavorite(int $userId, string $advertisementUuid): bool
    {
        return $this->repo->unfavorite($userId, $advertisementUuid);
    }

    public function listForUser(int $userId, array $params = [])
    {
        $perPage = (int) ($params['per_page'] ?? 20);

        return $this->repo->listForUser($userId, $perPage);
    }
}
