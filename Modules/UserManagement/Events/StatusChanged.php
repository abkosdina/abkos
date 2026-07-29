<?php

namespace Modules\UserManagement\Events;

use App\Models\User;

class StatusChanged
{
    public function __construct(public User $user, public string $status)
    {
    }
}
