<?php

namespace Modules\Advertisements\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementFilterResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'statuses' => $this->statuses ?? [],
            'visibilities' => $this->visibilities ?? [],
            'provinces' => $this->provinces ?? [],
            'cities' => $this->cities ?? [],
            'priorities' => $this->priorities ?? [],
        ];
    }
}
