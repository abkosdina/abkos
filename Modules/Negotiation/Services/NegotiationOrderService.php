<?php

namespace Modules\Negotiation\Services;

use Illuminate\Support\Str;
use Modules\Negotiation\Models\Negotiation;

/**
 * Deprecated compatibility service.
 *
 * This service is retained for legacy modules that may still expect an
 * order generation hook, but the core negotiation flow does not invoke it.
 */
class NegotiationOrderService
{
    public function createOrderForNegotiation(Negotiation $negotiation): array
    {
        $orderNumber = 'ORD-' . strtoupper(Str::random(8));

        return [
            'order_number' => $orderNumber,
            'negotiation_id' => $negotiation->id,
            'status' => 'pending_payment',
        ];
    }
}
