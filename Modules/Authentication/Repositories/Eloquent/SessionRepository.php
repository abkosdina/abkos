<?php

namespace Modules\Authentication\Repositories\Eloquent;

use Modules\Authentication\Models\UserSession;
use Modules\Authentication\Repositories\Interfaces\SessionRepositoryInterface;

class SessionRepository implements SessionRepositoryInterface
{
    public function __construct(protected UserSession $model)
    {
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->model->query()->where('user_id', $userId)->update(['is_active' => false]);
    }
}
