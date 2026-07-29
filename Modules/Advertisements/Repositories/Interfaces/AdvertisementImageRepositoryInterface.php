<?php

namespace Modules\Advertisements\Repositories\Interfaces;

interface AdvertisementImageRepositoryInterface
{
    public function create(array $data): object;
}
