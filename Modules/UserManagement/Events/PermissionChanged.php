<?php

namespace Modules\UserManagement\Events;

use App\Models\User;

class PermissionChanged
{
    public function __construct(public User $user)
    {
    }
}
