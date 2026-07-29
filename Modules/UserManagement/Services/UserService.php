<?php

namespace Modules\UserManagement\Services;

use Modules\UserManagement\Repositories\Interfaces\UserRepositoryInterface;

class UserService
{
    public function __construct(protected UserRepositoryInterface $repository)
    {
    }

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function create(array $data): object
    {
        return $this->repository->create($data);
    }

    public function find(int|string $id): ?object
    {
        return $this->repository->find($id);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->delete($id);
    }
}
