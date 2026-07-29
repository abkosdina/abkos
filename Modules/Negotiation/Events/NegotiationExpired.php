<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\Negotiation;

class NegotiationExpired
{
    public function __construct(public Negotiation $negotiation)
    {
    }
}
