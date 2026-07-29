<?php

namespace Modules\Negotiation\Actions;

use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Shared\Base\BaseAction;

class ExpireNegotiationAction extends BaseAction
{
    public function __construct(protected NegotiationService $service)
    {
    }

    protected function handle(...$arguments): mixed
    {
        [$uuid, $actorId] = $arguments;

        return $this->service->expireNegotiation($uuid, $actorId);
    }
}
