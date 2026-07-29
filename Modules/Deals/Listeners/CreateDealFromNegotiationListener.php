<?php

namespace Modules\Deals\Listeners;

use Modules\Negotiation\Events\NegotiationCompleted;
use Modules\Deals\Services\DealService;

class CreateDealFromNegotiationListener
{
    public function __construct(protected DealService $dealService)
    {
    }

    public function handle(NegotiationCompleted $event): void
    {
        try {
            $this->dealService->createDealFromNegotiation($event->negotiation);
        } catch (\Throwable $e) {
            // The deals module should not block negotiation completion.
            report($e);
        }
    }
}
