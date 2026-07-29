<?php

namespace Modules\Negotiation\Listeners;

use Modules\Negotiation\Events\OfferAccepted;

class LockAdvertisementListener
{
    public function handle(OfferAccepted $event): void
    {
        $advertisement = $event->offer->negotiation->advertisement;
        $advertisement->forceFill(['status' => 'Sold'])->save();
    }
}
