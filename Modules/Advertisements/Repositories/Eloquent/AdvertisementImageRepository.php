<?php

namespace Modules\Advertisements\Repositories\Eloquent;

use Modules\Advertisements\Models\AdvertisementImage;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementImageRepositoryInterface;

class AdvertisementImageRepository implements AdvertisementImageRepositoryInterface
{
    public function __construct(protected AdvertisementImage $model = new AdvertisementImage())
    {
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }
}
