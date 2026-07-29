<?php

namespace Modules\Negotiation\Listeners;

use Modules\Negotiation\Events\OfferAccepted;
use Modules\Negotiation\Services\NegotiationEscrowService;

/**
 * Deprecated compatibility listener.
 *
 * This listener is intentionally not wired into the current negotiation
 * event flow. It remains available only as a legacy artifact.
 */
class StartEscrowWorkflowListener
{
    public function __construct(protected NegotiationEscrowService $escrowService)
    {
    }

    public function handle(OfferAccepted $event): void
    {
        $this->escrowService->startEscrowForNegotiation($event->offer->negotiation);
    }
}
