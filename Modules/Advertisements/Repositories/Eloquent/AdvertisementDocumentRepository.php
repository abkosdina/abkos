<?php

namespace Modules\Advertisements\Repositories\Eloquent;

use Modules\Advertisements\Models\AdvertisementDocument;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementDocumentRepositoryInterface;

class AdvertisementDocumentRepository implements AdvertisementDocumentRepositoryInterface
{
    public function __construct(protected AdvertisementDocument $model = new AdvertisementDocument())
    {
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }
}
