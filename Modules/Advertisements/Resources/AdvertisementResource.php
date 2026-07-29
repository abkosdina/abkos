<?php

namespace Modules\Advertisements\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'advertisement_number' => $this->advertisement_number,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'priority' => $this->priority,
            'created_at' => $this->created_at,
            // include human-readable location when available
            'province' => $this->province?->name ?? null,
            'city' => $this->city?->name ?? null,
        ];
    }
}
