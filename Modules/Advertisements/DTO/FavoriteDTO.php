<?php

namespace Modules\Advertisements\DTO;

class FavoriteDTO
{
    public function __construct(public int $userId, public string $advertisementUuid)
    {
    }
}
