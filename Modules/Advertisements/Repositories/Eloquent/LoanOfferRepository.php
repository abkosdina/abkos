<?php

namespace Modules\Advertisements\Repositories\Eloquent;

use Modules\Advertisements\Models\LoanOffer;
use Modules\Advertisements\Repositories\Interfaces\LoanOfferRepositoryInterface;

class LoanOfferRepository implements LoanOfferRepositoryInterface
{
    public function __construct(protected LoanOffer $model = new LoanOffer())
    {
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->model->query()->findOrFail($id)->update($data);
    }
}
