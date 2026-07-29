<?php

namespace Modules\Negotiation\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NegotiationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'advertisement_id' => $this->advertisement_id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'status' => $this->status?->value ?? $this->status,
            'started_at' => $this->started_at,
            'accepted_at' => $this->accepted_at,
            'cancelled_at' => $this->cancelled_at,
            'expired_at' => $this->expired_at,
            'closed_at' => $this->closed_at,
            'selected_offer_id' => $this->selected_offer_id,
            'agreed_price' => $this->agreed_price,
            'order_id' => $this->order_id,
        ];
    }
}
