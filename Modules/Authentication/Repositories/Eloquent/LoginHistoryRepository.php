<?php

namespace Modules\Authentication\Repositories\Eloquent;

use Modules\Authentication\Models\LoginHistory;
use Modules\Authentication\Repositories\Interfaces\LoginHistoryRepositoryInterface;

class LoginHistoryRepository implements LoginHistoryRepositoryInterface
{
    public function __construct(protected LoginHistory $model)
    {
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }
}
