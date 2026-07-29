<?php

namespace Modules\Negotiation\Policies;

use App\Models\User;
use Modules\Negotiation\Models\Negotiation;

class NegotiationPolicy
{
    public function view(User $user, Negotiation $negotiation): bool
    {
        return $user->id === $negotiation->buyer_id || $user->id === $negotiation->seller_id || $user->hasPermissionTo('negotiation.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('negotiation.create') || $user->hasPermissionTo('negotiation.offer');
    }

    public function offer(User $user, Negotiation $negotiation): bool
    {
        return $user->id === $negotiation->buyer_id || $user->hasPermissionTo('negotiation.offer');
    }

    public function accept(User $user, Negotiation $negotiation): bool
    {
        return $user->id === $negotiation->seller_id || $user->hasPermissionTo('negotiation.accept');
    }

    public function reject(User $user, Negotiation $negotiation): bool
    {
        return $user->id === $negotiation->seller_id || $user->hasPermissionTo('negotiation.reject');
    }

    public function cancel(User $user, Negotiation $negotiation): bool
    {
        return $user->id === $negotiation->buyer_id || $user->id === $negotiation->seller_id || $user->hasPermissionTo('negotiation.cancel');
    }

    public function convert(User $user, Negotiation $negotiation): bool
    {
        return $user->hasPermissionTo('negotiation.convert');
    }
}
