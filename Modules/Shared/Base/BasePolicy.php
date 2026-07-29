<?php

namespace Modules\Shared\Base;

use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;
}
