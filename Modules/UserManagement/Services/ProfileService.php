<?php

namespace Modules\UserManagement\Services;

use Modules\UserManagement\Repositories\Interfaces\ProfileRepositoryInterface;

class ProfileService
{
    public function __construct(protected ProfileRepositoryInterface $repository)
    {
    }
}
