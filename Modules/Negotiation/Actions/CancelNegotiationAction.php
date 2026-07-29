<?php

namespace Modules\Negotiation\Actions;

use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Shared\Base\BaseAction;

class CancelNegotiationAction extends BaseAction
{
    public function __construct(protected NegotiationService $service)
    {
    }

    protected function handle(...$arguments): mixed
    {
        [$uuid, $actorId] = $arguments;

        return $this->service->cancelNegotiation($uuid, $actorId);
    }
}
