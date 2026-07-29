<?php

namespace Modules\Advertisements\Actions;

use Modules\Advertisements\DTO\AdvertisementDTO;
use Modules\Advertisements\Services\AdvertisementService;

class CreateAdvertisementAction
{
    public function __construct(protected AdvertisementService $service)
    {
    }

    public function execute(AdvertisementDTO $dto): object
    {
        return $this->service->create($dto);
    }
}
