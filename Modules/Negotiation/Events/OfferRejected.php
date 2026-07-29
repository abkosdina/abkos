<?php

namespace Modules\Negotiation\Events;

class OfferRejected
{
    public function __construct(public \Modules\Negotiation\Models\NegotiationOffer $offer)
    {
    }
}
