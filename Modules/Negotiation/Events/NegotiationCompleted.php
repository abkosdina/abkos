<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Models\NegotiationOffer;

class NegotiationCompleted
{
    public function __construct(public Negotiation $negotiation, public NegotiationOffer $offer)
    {
    }
}
