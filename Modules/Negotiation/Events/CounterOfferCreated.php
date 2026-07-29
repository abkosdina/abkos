<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\NegotiationOffer;

class CounterOfferCreated
{
    public function __construct(public NegotiationOffer $offer)
    {
    }
}
