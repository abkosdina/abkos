<?php

namespace Modules\UserManagement\Events;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleAssigned
{
    public function __construct(public User $user, public Role $role)
    {
    }
}
