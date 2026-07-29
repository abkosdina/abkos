<?php

namespace Modules\Negotiation\Tests\Unit;

use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Services\NegotiationWorkflowService;
use Tests\TestCase;

class NegotiationWorkflowServiceTest extends TestCase
{
    public function test_complete_negotiation_marks_negotiation_as_accepted_and_sets_accepted_and_closed_timestamps(): void
    {
        $negotiation = $this->getMockBuilder(Negotiation::class)
            ->onlyMethods(['save'])
            ->getMock();

        $negotiation->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $negotiation->forceFill(['id' => 1, 'status' => NegotiationStatus::Active]);

        $offer = $this->getMockBuilder(NegotiationOffer::class)
            ->onlyMethods([])
            ->getMock();
        $offer->forceFill(['id' => 5, 'amount' => 100.00]);

        $stateService = $this->createMock(\Modules\Negotiation\Services\NegotiationStateService::class);
        $stateService->expects($this->once())
            ->method('transition')
            ->with($negotiation, NegotiationStatus::Accepted)
            ->willReturn($negotiation);

        $historyRepository = $this->createMock(\Modules\Negotiation\Repositories\Interfaces\NegotiationHistoryRepositoryInterface::class);
        $historyRepository->expects($this->once())
            ->method('log')
            ->with($negotiation->id, 1, 'negotiation_completed', ['offer_id' => 5]);

        $service = new NegotiationWorkflowService(
            $this->createMock(\Modules\Negotiation\Repositories\Interfaces\NegotiationRepositoryInterface::class),
            $this->createMock(\Modules\Negotiation\Repositories\Interfaces\NegotiationOfferRepositoryInterface::class),
            $historyRepository,
            $stateService,
        );

        $service->completeNegotiation($negotiation, $offer, 1);

        $this->assertSame(NegotiationStatus::Accepted, $negotiation->status);
        $this->assertNotNull($negotiation->accepted_at);
        $this->assertNotNull($negotiation->closed_at);
        $this->assertSame(5, $negotiation->selected_offer_id);
        $this->assertSame('100.00', (string) $negotiation->agreed_price);
    }
}
