<?php

namespace Modules\Advertisements\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdvertisementRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $advertisement, public $user = null, public array $payload = [])
    {
    }
}
