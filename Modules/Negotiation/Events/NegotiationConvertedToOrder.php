<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\Negotiation;

/**
 * Deprecated compatibility event.
 *
 * This event remains in the codebase for legacy references only.
 * It is not emitted by the current negotiation terminal flow.
 */
class NegotiationConvertedToOrder
{
    public function __construct(public Negotiation $negotiation)
    {
    }
}
