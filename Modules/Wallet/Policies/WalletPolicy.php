<?php

namespace Modules\Wallet\Policies;

use App\Models\User;
use Modules\Wallet\Models\Wallet;

class WalletPolicy
{
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id || $user->hasPermissionTo('View Wallet');
    }

    public function deposit(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id || $user->hasPermissionTo('Deposit');
    }

    public function withdraw(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id || $user->hasPermissionTo('Withdraw');
    }

    public function freeze(User $user, Wallet $wallet): bool
    {
        return $user->hasPermissionTo('Freeze Wallet');
    }

    public function unlock(User $user, Wallet $wallet): bool
    {
        return $user->hasPermissionTo('Unlock Wallet');
    }
}
