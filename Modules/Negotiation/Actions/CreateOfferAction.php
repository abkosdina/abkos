<?php

namespace Modules\Negotiation\Actions;

use Modules\Negotiation\DTO\NegotiationOfferDTO;
use Modules\Negotiation\Models\NegotiationOffer;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Shared\Base\BaseAction;

class CreateOfferAction extends BaseAction
{
    public function __construct(protected NegotiationService $service)
    {
    }

    protected function handle(...$arguments): mixed
    {
        [$negotiationId, $dto] = $arguments;

        return $this->service->createOffer($negotiationId, $dto);
    }
}
