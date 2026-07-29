<?php

namespace Modules\Negotiation\Listeners;

use Modules\Negotiation\Events\OfferAccepted;
use Modules\Negotiation\Services\NegotiationOrderService;

/**
 * Deprecated compatibility listener.
 *
 * This listener is intentionally not wired into the current negotiation
 * event flow. It remains available only as a legacy artifact.
 */
class CreateOrderListener
{
    public function __construct(protected NegotiationOrderService $orderService)
    {
    }

    public function handle(OfferAccepted $event): void
    {
        $order = $this->orderService->createOrderForNegotiation($event->offer->negotiation);
        $event->offer->negotiation->forceFill(['order_id' => $order['negotiation_id']])->save();
    }
}
