<?php

namespace Modules\UserManagement\Events;

use App\Models\User;

class UserDeleted
{
    public function __construct(public User $user)
    {
    }
}
