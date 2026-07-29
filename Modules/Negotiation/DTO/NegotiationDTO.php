<?php

namespace Modules\Negotiation\DTO;

class NegotiationDTO
{
    public function __construct(
        public int $advertisementId,
        public int $buyerId,
        public int $sellerId,
        public ?int $conversationId = null,
        public ?string $status = null,
    ) {
    }
}
