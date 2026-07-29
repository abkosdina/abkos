<?php

namespace Modules\Negotiation\Actions;

use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Shared\Base\BaseAction;

/**
 * Legacy compatibility extension point.
 *
 * This action is intentionally a no-op in the current negotiation flow.
 * Negotiation acceptance no longer creates an order automatically.
 *
 * External systems may still resolve this action if they wish to integrate
 * order creation or similar behavior from legacy code paths.
 */
class ConvertNegotiationToOrderAction extends BaseAction
{
    public function __construct(protected NegotiationService $service)
    {
    }

    protected function handle(...$arguments): mixed
    {
        [$negotiation, $actorId] = $arguments;

        return $negotiation;
    }
}
