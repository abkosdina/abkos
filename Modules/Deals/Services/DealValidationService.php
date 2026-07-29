<?php

namespace Modules\Deals\Services;

use App\Models\User;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;
use Modules\Deals\Enums\DealStatus;
use Modules\Deals\Repositories\Interfaces\DealRepositoryInterface;
use Modules\KYC\Services\KycAccessService;
use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Models\Negotiation;

class DealValidationService
{
    public function __construct(
        protected KycAccessService $kycAccessService,
        protected DealRepositoryInterface $dealRepository
    ) {
    }

    public function validateNegotiationForDeal(Negotiation $negotiation): void
    {
        $advertisement = $negotiation->advertisement;
        $buyer = $negotiation->buyer;
        $seller = $negotiation->seller;

        if (! $advertisement) {
            throw new \InvalidArgumentException('Negotiation advertisement not found.');
        }

        if ($negotiation->status !== NegotiationStatus::Accepted) {
            throw new \InvalidArgumentException('Negotiation must be accepted to create a deal.');
        }

        $statusValue = $advertisement->status instanceof AdvertisementStatus
            ? $advertisement->status->value
            : (string) ($advertisement->status ?? '');

        if ($statusValue !== AdvertisementStatus::Published->value) {
            throw new \InvalidArgumentException('Advertisement must be published to create a deal.');
        }

        if (in_array($statusValue, [AdvertisementStatus::Archived->value, AdvertisementStatus::Sold->value, AdvertisementStatus::Deleted->value], true)) {
            throw new \InvalidArgumentException('Advertisement is not available for deal creation.');
        }

        if (! $buyer || ! $seller) {
            throw new \InvalidArgumentException('Deal participants are invalid.');
        }

        if (! $this->kycAccessService->isApproved($buyer)) {
            throw new \InvalidArgumentException('Buyer KYC must be approved.');
        }

        if (! $this->kycAccessService->isApproved($seller)) {
            throw new \InvalidArgumentException('Seller KYC must be approved.');
        }

        if ($this->dealRepository->existsActiveDealForNegotiation($negotiation->id)) {
            throw new \InvalidArgumentException('An active deal already exists for this negotiation.');
        }

        if ($this->dealRepository->existsActiveDealForAdvertisement($advertisement->id)) {
            throw new \InvalidArgumentException('An active deal already exists for this advertisement.');
        }
    }
}
