<?php

namespace Modules\Deals\Policies;

use App\Models\User;
use Modules\Deals\Models\Deal;

class DealPolicy
{
    public function view(User $user, Deal $deal): bool
    {
        return $user->id === $deal->buyer_id || $user->id === $deal->seller_id || $user->hasPermissionTo('menu.deals');
    }

    public function cancel(User $user, Deal $deal): bool
    {
        return $user->id === $deal->buyer_id || $user->id === $deal->seller_id || $user->hasPermissionTo('menu.deals');
    }

    public function close(User $user, Deal $deal): bool
    {
        return $user->id === $deal->seller_id || $user->hasPermissionTo('menu.deals');
    }

    public function expire(User $user, Deal $deal): bool
    {
        return $user->hasRole('Super Admin') || $user->hasPermissionTo('menu.deals');
    }

    public function dispute(User $user, Deal $deal): bool
    {
        return $user->id === $deal->buyer_id || $user->id === $deal->seller_id || $user->hasPermissionTo('menu.deals');
    }

    public function complete(User $user, Deal $deal): bool
    {
        return $user->id === $deal->seller_id || $user->hasPermissionTo('menu.deals');
    }
}
