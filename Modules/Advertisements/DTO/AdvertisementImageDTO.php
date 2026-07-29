<?php

namespace Modules\Advertisements\DTO;

class AdvertisementImageDTO
{
    public function __construct(
        public int|string $mediaId,
        public ?int $sortOrder = 0,
        public bool $isCover = false,
    ) {
    }

    public function toArray(): array
    {
        return [
            'media_id' => $this->mediaId,
            'sort_order' => $this->sortOrder,
            'is_cover' => $this->isCover,
        ];
    }
}
