<?php

namespace Modules\Advertisements\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AdvertisementViewed
{
    use Dispatchable;

    public function __construct(public ?int $userId, public ?int $advertisementId, public ?string $ip = null, public ?string $device = null)
    {
    }
}
