<?php

namespace Modules\UserManagement\Services;

use Modules\UserManagement\Repositories\Interfaces\PermissionRepositoryInterface;

class PermissionService
{
    public function __construct(protected PermissionRepositoryInterface $repository)
    {
    }
}
