<?php

namespace Modules\Negotiation\Events;

use Modules\Negotiation\Models\NegotiationOffer;

/**
 * Deprecated event.
 *
 * Historically dispatched when an offer was accepted. The current terminal
 * negotiation agreement path now emits only NegotiationCompleted.
 */
class OfferAccepted
{
    public function __construct(public NegotiationOffer $offer)
    {
    }
}
