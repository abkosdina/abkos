<?php

namespace Modules\UserManagement\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function manage(User $user): bool
    {
        return $user->hasRole('administrator');
    }
}
