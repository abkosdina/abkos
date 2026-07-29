<?php

namespace Modules\Authentication\Repositories\Interfaces;

interface SessionRepositoryInterface
{
    public function create(array $data): object;

    public function revokeAllForUser(int $userId): void;
}
