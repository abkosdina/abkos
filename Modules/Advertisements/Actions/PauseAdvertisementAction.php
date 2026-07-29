<?php

namespace Modules\Advertisements\Actions;

use Modules\Advertisements\Services\AdvertisementService;

class PauseAdvertisementAction
{
    public function __construct(protected AdvertisementService $service)
    {
    }

    public function execute(int|string $id): object
    {
        return $this->service->pause($id);
    }
}
