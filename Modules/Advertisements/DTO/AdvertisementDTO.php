<?php

namespace Modules\Advertisements\DTO;

class AdvertisementDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public ?string $shortDescription = null,
        public ?int $provinceId = null,
        public ?int $cityId = null,
        public ?string $status = 'Draft',
        public ?string $visibility = 'Public',
        public ?int $priority = 0,
        public ?int $userId = null,
        public ?int $loanProductId = null,
        public ?LoanOfferDTO $loanOffer = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->shortDescription,
            'province_id' => $this->provinceId,
            'city_id' => $this->cityId,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'priority' => $this->priority,
            'loan_product_id' => $this->loanProductId,
            'user_id' => $this->userId,
        ];
    }

    public function hasLocation(): bool
    {
        return ! empty($this->provinceId) && ! empty($this->cityId);
    }
}
