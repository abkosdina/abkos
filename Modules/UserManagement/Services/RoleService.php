<?php

namespace Modules\UserManagement\Services;

use Modules\UserManagement\Repositories\Interfaces\RoleRepositoryInterface;

class RoleService
{
    public function __construct(protected RoleRepositoryInterface $repository)
    {
    }

    public function all(): array
    {
        return $this->repository->all();
    }
}
