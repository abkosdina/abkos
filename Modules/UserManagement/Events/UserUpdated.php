<?php

namespace Modules\UserManagement\Events;

use App\Models\User;

class UserUpdated
{
    public function __construct(public User $user)
    {
    }
}
