<?php

namespace Modules\Advertisements\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'advertisement_number' => $this->advertisement_number,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'visibility' => $this->visibility->value,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
            'views_count' => $this->views_count,
            'contacts_count' => $this->contacts_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
