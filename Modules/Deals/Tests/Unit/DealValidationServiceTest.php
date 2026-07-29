<?php

namespace Modules\Deals\Tests\Unit;

use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Deals\Enums\DealStatus;
use Modules\Deals\Repositories\Interfaces\DealRepositoryInterface;
use Modules\Deals\Services\DealValidationService;
use Modules\KYC\Services\KycAccessService;
use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Advertisements\Models\Advertisement;
use App\Models\User;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DealValidationServiceTest extends TestCase
{
    private KycAccessService|MockObject $kycAccessService;
    private DealRepositoryInterface|MockObject $dealRepository;
    private DealValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kycAccessService = $this->createMock(KycAccessService::class);
        $this->dealRepository = $this->createMock(DealRepositoryInterface::class);
        $this->service = new DealValidationService(
            $this->kycAccessService,
            $this->dealRepository
        );
    }

    public function test_validate_negotiation_for_deal_allows_valid_accepted_negotiation(): void
    {
        $advertisement = $this->createMock(Advertisement::class);
        $advertisement->method('__get')->willReturnMap([
            ['status', AdvertisementStatus::Published],
            ['id', 10],
            ['seller_user_id', 2],
        ]);

        $buyer = $this->createMock(User::class);
        $buyer->method('getAttribute')->with('id')->willReturn(1);

        $seller = $this->createMock(User::class);
        $seller->method('getAttribute')->with('id')->willReturn(2);

        $negotiation = $this->getMockBuilder(Negotiation::class)
            ->onlyMethods(['__get'])
            ->getMock();

        $negotiation->method('__get')->willReturnMap([
            ['status', NegotiationStatus::Accepted],
            ['advertisement', $advertisement],
            ['buyer', $buyer],
            ['seller', $seller],
            ['id', 5],
            ['advertisement_id', 10],
        ]);

        $this->kycAccessService->expects($this->exactly(2))
            ->method('isApproved')
            ->willReturn(true);

        $this->dealRepository->expects($this->once())->method('existsActiveDealForNegotiation')->with(5)->willReturn(false);
        $this->dealRepository->expects($this->once())->method('existsActiveDealForAdvertisement')->with(10)->willReturn(false);

        $this->service->validateNegotiationForDeal($negotiation);

        $this->assertTrue(true);
    }
}
