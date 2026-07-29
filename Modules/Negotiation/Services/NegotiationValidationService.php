<?php

namespace Modules\Negotiation\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;
use Modules\KYC\Services\KycAccessService;
use Modules\Negotiation\Enums\NegotiationStatus;

class NegotiationValidationService
{
    public function validateNegotiationCreation(User $buyer, Advertisement $advertisement): void
    {
        $buyerId = $buyer->getAttribute('id');
        $advertisementOwnerId = $advertisement->getAttribute('user_id');
        $advertisementSellerId = $advertisement->getAttribute('seller_user_id');

        if ($advertisementOwnerId === $buyerId || $advertisementSellerId === $buyerId) {
            throw new \InvalidArgumentException('Buyer cannot negotiate their own advertisement.');
        }

        if ($advertisement->status instanceof AdvertisementStatus) {
            $statusValue = $advertisement->status->value;
        } else {
            $statusValue = (string) ($advertisement->status ?? '');
        }

        if ($statusValue !== AdvertisementStatus::Published->value) {
            throw new \InvalidArgumentException('Advertisement must be published before negotiation can start.');
        }

        if (method_exists($advertisement, 'trashed') && $advertisement->trashed()) {
            throw new \InvalidArgumentException('Advertisement cannot be deleted.');
        }

        if ($advertisement->status === AdvertisementStatus::Archived || $advertisement->status === AdvertisementStatus::Sold || $advertisement->status === AdvertisementStatus::Deleted) {
            throw new \InvalidArgumentException('Advertisement is not available for negotiation.');
        }

        $existingNegotiation = \Modules\Negotiation\Models\Negotiation::query()
            ->where('advertisement_id', $advertisement->id)
            ->whereIn('status', [
                NegotiationStatus::Pending->value,
                NegotiationStatus::Active->value,
                NegotiationStatus::WaitingBuyer->value,
                NegotiationStatus::WaitingSeller->value,
            ])
            ->exists();

        if ($existingNegotiation) {
            throw new \InvalidArgumentException('A negotiation is already active for this advertisement.');
        }
    }

    public function validateOfferCreation(User $user, \Modules\Negotiation\Models\Negotiation $negotiation, float $amount): void
    {
        if ($user->id !== $negotiation->buyer_id) {
            throw new \InvalidArgumentException('Only the buyer can create an offer.');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Offer amount must be greater than zero.');
        }

        $pendingOffers = $negotiation->offers()->whereIn('status', ['Pending', 'CounterOffer'])->count();
        if ($pendingOffers > 0) {
            throw new \InvalidArgumentException('Only one pending offer is allowed.');
        }
    }

    public function validateOfferAcceptance(User $user, \Modules\Negotiation\Models\Negotiation $negotiation): void
    {
        if ($user->id !== $negotiation->seller_id) {
            throw new \InvalidArgumentException('Only the seller can accept an offer.');
        }

        if ($negotiation->status !== NegotiationStatus::Active && $negotiation->status !== NegotiationStatus::WaitingBuyer && $negotiation->status !== NegotiationStatus::WaitingSeller) {
            throw new \InvalidArgumentException('Negotiation is not in an actionable state.');
        }
    }
}
