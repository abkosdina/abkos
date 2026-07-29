<?php

namespace Modules\UserManagement\Services;

use Modules\UserManagement\Repositories\Interfaces\BankAccountRepositoryInterface;

class BankAccountService
{
    public function __construct(protected BankAccountRepositoryInterface $repository)
    {
    }
}
