<?php

namespace Modules\Authentication\Events;

class UserLoggedIn
{
    public function __construct(public int $userId)
    {
    }
}
