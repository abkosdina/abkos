<?php

namespace Modules\Advertisements\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementPublished
{
    use Dispatchable;

    public function __construct(public Advertisement $advertisement)
    {
    }
}
