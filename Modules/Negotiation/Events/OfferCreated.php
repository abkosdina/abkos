<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\NegotiationOffer;

class OfferCreated
{
    public function __construct(public NegotiationOffer $offer)
    {
    }
}
