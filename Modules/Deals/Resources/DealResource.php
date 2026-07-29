<?php

namespace Modules\Deals\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'negotiation_id' => $this->negotiation_id,
            'advertisement_id' => $this->advertisement_id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'status' => $this->status?->value ?? $this->status,
            'agreed_price' => $this->agreed_price,
            'accepted_at' => $this->accepted_at,
            'closed_at' => $this->closed_at,
            'workflow_instance_id' => $this->workflow_instance_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
