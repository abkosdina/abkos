<?php

namespace Modules\Negotiation\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NegotiationOfferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'negotiation_id' => $this->negotiation_id,
            'created_by' => $this->created_by,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status?->value ?? $this->status,
            'expires_at' => $this->expires_at,
            'accepted_at' => $this->accepted_at,
            'rejected_at' => $this->rejected_at,
        ];
    }
}
