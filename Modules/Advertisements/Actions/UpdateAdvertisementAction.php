<?php

namespace Modules\Advertisements\Actions;

use Modules\Advertisements\Services\AdvertisementService;

class UpdateAdvertisementAction
{
    public function __construct(protected AdvertisementService $service)
    {
    }

    public function execute(int|string $id, array $data): object
    {
        return $this->service->update($id, $data);
    }
}
