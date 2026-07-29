<?php

namespace Modules\Negotiation\DTO;

class NegotiationOfferDTO
{
    public function __construct(
        public int $negotiationId,
        public int $createdBy,
        public float $amount,
        public ?string $description = null,
        public ?string $status = null,
        public ?string $expiresAt = null,
    ) {
    }
}
