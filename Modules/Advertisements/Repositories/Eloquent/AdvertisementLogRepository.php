<?php

namespace Modules\Advertisements\Repositories\Eloquent;

use Modules\Advertisements\Models\AdvertisementLog;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementLogRepositoryInterface;

class AdvertisementLogRepository implements AdvertisementLogRepositoryInterface
{
    public function __construct(protected AdvertisementLog $model = new AdvertisementLog())
    {
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }
}
