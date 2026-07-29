<?php

namespace Modules\Negotiation\Actions;

use Modules\Negotiation\DTO\NegotiationOfferDTO;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Shared\Base\BaseAction;

class CounterOfferAction extends BaseAction
{
    public function __construct(protected NegotiationService $service)
    {
    }

    protected function handle(...$arguments): mixed
    {
        [$offerUuid, $dto] = $arguments;

        return $this->service->counterOffer($offerUuid, $dto);
    }
}
