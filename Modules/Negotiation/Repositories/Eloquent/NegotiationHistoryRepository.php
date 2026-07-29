<?php

namespace Modules\Negotiation\Repositories\Eloquent;

use Modules\Negotiation\Models\NegotiationHistory;
use Modules\Negotiation\Repositories\Interfaces\NegotiationHistoryRepositoryInterface;

class NegotiationHistoryRepository implements NegotiationHistoryRepositoryInterface
{
    public function create(array $data): NegotiationHistory
    {
        return NegotiationHistory::query()->create($data);
    }

    public function log(int $negotiationId, int $actorId, string $event, array $details = [], array $metadata = []): NegotiationHistory
    {
        return $this->create([
            'negotiation_id' => $negotiationId,
            'actor_id' => $actorId,
            'event' => $event,
            'details' => $details,
            'metadata' => $metadata,
        ]);
    }
}
