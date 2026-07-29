<?php

namespace Modules\Negotiation\Services;

use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Models\Negotiation;

class NegotiationStateService
{
    public function transition(Negotiation $negotiation, NegotiationStatus $targetStatus): Negotiation
    {
        $allowed = match ($negotiation->status) {
            NegotiationStatus::Pending => [NegotiationStatus::Active, NegotiationStatus::WaitingBuyer, NegotiationStatus::WaitingSeller, NegotiationStatus::Cancelled, NegotiationStatus::Expired],
            NegotiationStatus::Active => [NegotiationStatus::WaitingBuyer, NegotiationStatus::WaitingSeller, NegotiationStatus::Accepted, NegotiationStatus::Cancelled, NegotiationStatus::Expired],
            NegotiationStatus::WaitingBuyer => [NegotiationStatus::WaitingSeller, NegotiationStatus::Accepted, NegotiationStatus::Cancelled, NegotiationStatus::Expired],
            NegotiationStatus::WaitingSeller => [NegotiationStatus::WaitingBuyer, NegotiationStatus::Accepted, NegotiationStatus::Cancelled, NegotiationStatus::Expired],
            NegotiationStatus::Accepted => [NegotiationStatus::Closed],
            NegotiationStatus::ConvertedToOrder => [NegotiationStatus::Closed],
            NegotiationStatus::Cancelled => [],
            NegotiationStatus::Expired => [],
            NegotiationStatus::Rejected => [],
            NegotiationStatus::Closed => [],
            default => [],
        };

        if (! in_array($targetStatus, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid negotiation state transition.');
        }

        $negotiation->fill(['status' => $targetStatus]);
        $negotiation->save();

        return $negotiation;
    }
}
