<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Modules\Advertisements\DTO\AdvertisementDTO;
use Modules\Advertisements\DTO\LoanOfferDTO;
use Modules\Advertisements\Services\AdvertisementService;
use Tests\TestCase;

class AdvertisementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_advertisement_with_a_loan_offer(): void
    {
        $user = User::factory()->create();

        $service = app(AdvertisementService::class);

        $dto = new AdvertisementDTO(
            title: 'Luxury Apartment Loan',
            description: 'A premium loan offer for apartment financing.',
            shortDescription: 'Premium apartment financing',
            provinceId: 1,
            cityId: 1,
            status: 'Draft',
            visibility: 'Public',
            priority: 1,
            userId: $user->id,
            loanOffer: new LoanOfferDTO(
                bankId: 10,
                loanPlanId: 20,
                branchId: 30,
                loanTypeId: 40,
                loanAmount: 500000000,
                salePrice: 450000000,
                interestRate: 18.5,
                installmentCount: 24,
                monthlyInstallment: 25000000,
                totalRepayment: 600000000,
                remainingInstallments: 24,
                guarantorRequired: true,
                guarantorCount: 1,
                checkRequired: false,
                promissoryNoteRequired: false,
                collateralRequired: false,
                transferFee: 0,
                additionalCost: 50000,
                isNegotiable: true,
                escrowEnabled: true,
                vipGuarantee: false,
                contractReady: true,
                isOnline: true,
                isInPerson: true,
            ),
        );

        $result = $service->create($dto);

        $this->assertNotNull($result->advertisement);
        $this->assertNotNull($result->loanOffer);
        $this->assertSame('Draft', $result->advertisement->status);
        $this->assertSame($user->id, $result->advertisement->user_id);
        $this->assertDatabaseHas('advertisements', ['title' => 'Luxury Apartment Loan']);
        $this->assertDatabaseHas('loan_offers', ['advertisement_id' => $result->advertisement->id]);
    }
}
