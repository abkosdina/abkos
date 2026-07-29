<?php

namespace Modules\Negotiation\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NegotiationDetailResource extends JsonResource
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
            'offers' => NegotiationOfferResource::collection($this->whenLoaded('offers')),
            'agreed_price' => $this->agreed_price,
            'histories' => $this->whenLoaded('histories'),
        ];
    }
}
