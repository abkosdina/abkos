<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\Negotiation;

class NegotiationCreated
{
    public function __construct(public Negotiation $negotiation)
    {
    }
}
