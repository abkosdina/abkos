<?php

namespace Modules\Shared\Base;

use Modules\Shared\Contracts\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return $this->getQuery()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->getQuery()->find($id, $columns);
    }

    public function create(array $data): object
    {
        return $this->getQuery()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->getQuery()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return $this->getQuery()->findOrFail($id)->delete();
    }

    abstract protected function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
