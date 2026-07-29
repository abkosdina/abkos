<?php

namespace Modules\UserManagement\Repositories\Eloquent;

use Modules\UserManagement\Repositories\Interfaces\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function all(): array
    {
        return Role::query()->get()->all();
    }
}
