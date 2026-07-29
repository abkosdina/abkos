<?php

namespace Modules\Authentication\Repositories\Interfaces;

interface LoginHistoryRepositoryInterface
{
    public function create(array $data): object;
}
