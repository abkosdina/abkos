<?php

namespace Modules\Negotiation\Actions;

use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Shared\Base\BaseAction;

class AcceptOfferAction extends BaseAction
{
    public function __construct(protected NegotiationService $service)
    {
    }

    protected function handle(...$arguments): mixed
    {
        [$offerUuid, $actorId] = $arguments;

        return $this->service->acceptOffer($offerUuid, $actorId);
    }
}
