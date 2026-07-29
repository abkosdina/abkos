<?php

namespace Modules\Advertisements\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementResumed
{
    use Dispatchable;

    public function __construct(public Advertisement $advertisement)
    {
    }
}
