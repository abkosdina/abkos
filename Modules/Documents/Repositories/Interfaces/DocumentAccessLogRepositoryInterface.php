<?php

namespace Modules\Documents\Repositories\Interfaces;

interface DocumentAccessLogRepositoryInterface
{
    public function create(array $data): object;
}
