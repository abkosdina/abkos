<?php

namespace Modules\Authentication\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionPolicy
{
    use HandlesAuthorization;

    public function manage(User $user): bool
    {
        return $user->hasRole('administrator') || $user->hasRole('operator');
    }
}
