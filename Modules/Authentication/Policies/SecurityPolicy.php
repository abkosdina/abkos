<?php

namespace Modules\Authentication\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SecurityPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->hasRole('administrator') || $user->hasRole('operator');
    }
}
