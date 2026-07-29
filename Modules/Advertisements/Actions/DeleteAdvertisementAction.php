<?php

namespace Modules\Advertisements\Actions;

use Modules\Advertisements\Services\AdvertisementService;

class DeleteAdvertisementAction
{
    public function __construct(protected AdvertisementService $service)
    {
    }

    public function execute(int|string $id): bool
    {
        return $this->service->delete($id);
    }
}
