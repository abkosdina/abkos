<?php

namespace Modules\Documents\Repositories\Interfaces;

interface DocumentDownloadLogRepositoryInterface
{
    public function create(array $data): object;
}
