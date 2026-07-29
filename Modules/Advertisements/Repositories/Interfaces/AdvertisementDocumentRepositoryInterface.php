<?php

namespace Modules\Advertisements\Repositories\Interfaces;

interface AdvertisementDocumentRepositoryInterface
{
    public function create(array $data): object;
}
