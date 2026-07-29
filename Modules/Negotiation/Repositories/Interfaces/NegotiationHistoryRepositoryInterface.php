<?php

namespace Modules\Negotiation\Repositories\Interfaces;

use Modules\Negotiation\Models\NegotiationHistory;

interface NegotiationHistoryRepositoryInterface
{
    public function create(array $data): NegotiationHistory;

    public function log(int $negotiationId, int $actorId, string $event, array $details = [], array $metadata = []): NegotiationHistory;
}
