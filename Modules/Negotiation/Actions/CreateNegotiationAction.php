<?php

namespace Modules\Negotiation\Actions;

use Modules\Negotiation\DTO\NegotiationDTO;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Shared\Base\BaseAction;

class CreateNegotiationAction extends BaseAction
{
    public function __construct(protected NegotiationService $service)
    {
    }

    protected function handle(...$arguments): mixed
    {
        [$dto] = $arguments;

        return $this->service->createNegotiation($dto);
    }
}
