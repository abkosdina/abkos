<?php

namespace Modules\Advertisements\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AdvertisementFavorited
{
    use Dispatchable;

    public function __construct(public int $userId, public string $advertisementUuid)
    {
    }
}
