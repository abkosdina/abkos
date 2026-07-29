<?php

namespace Modules\Advertisements\Actions;

use Modules\Advertisements\Services\AdvertisementService;

class ArchiveAdvertisementAction
{
    public function __construct(protected AdvertisementService $service)
    {
    }

    public function execute(int|string $id): object
    {
        return $this->service->archive($id);
    }
}
