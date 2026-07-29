<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\Negotiation;

class NegotiationCancelled
{
    public function __construct(public Negotiation $negotiation)
    {
    }
}
