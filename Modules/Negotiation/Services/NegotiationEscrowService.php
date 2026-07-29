<?php

namespace Modules\Negotiation\Services;

use Modules\Negotiation\Models\Negotiation;

/**
 * Deprecated compatibility service.
 *
 * This service is retained to preserve legacy references, but core negotiation
 * completion does not start escrow workflows automatically.
 */
class NegotiationEscrowService
{
    public function startEscrowForNegotiation(Negotiation $negotiation): array
    {
        return [
            'negotiation_id' => $negotiation->id,
            'status' => 'pending_funding',
        ];
    }
}
