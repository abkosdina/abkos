<?php

namespace Modules\Documents\Repositories\Interfaces;

interface DocumentLinkRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id, array $columns = ['*']): ?object;
}
