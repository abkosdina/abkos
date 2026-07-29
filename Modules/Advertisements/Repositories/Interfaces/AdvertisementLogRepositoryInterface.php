<?php

namespace Modules\Advertisements\Repositories\Interfaces;

interface AdvertisementLogRepositoryInterface
{
    public function create(array $data): object;
}
