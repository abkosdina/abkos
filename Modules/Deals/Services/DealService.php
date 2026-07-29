<?php

namespace Modules\Deals\Services;

use Modules\Deals\Models\Deal;
use Modules\Deals\Repositories\Interfaces\DealRepositoryInterface;
use Modules\Negotiation\Models\Negotiation;

class DealService
{
    public function __construct(
        protected DealRepositoryInterface $dealRepository,
        protected DealValidationService $validationService,
        protected DealWorkflowService $workflowService
    ) {
    }

    public function createDealFromNegotiation(Negotiation $negotiation): Deal
    {
        $this->validationService->validateNegotiationForDeal($negotiation);

        $deal = $this->dealRepository->create([
            'negotiation_id' => $negotiation->id,
            'advertisement_id' => $negotiation->advertisement_id,
            'buyer_id' => $negotiation->buyer_id,
            'seller_id' => $negotiation->seller_id,
            'agreed_price' => $negotiation->agreed_price,
            'status' => 'Pending',
            'accepted_at' => $negotiation->accepted_at,
            'closed_at' => $negotiation->closed_at,
        ]);

        $workflowInstance = $this->workflowService->createDealWorkflow($deal);
        $deal->workflow_instance_id = $workflowInstance->id;
        $deal->save();

        return $deal;
    }
}
