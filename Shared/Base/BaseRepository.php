<?php

namespace Shared\Base;

abstract class BaseRepository
{
    protected mixed $model;

    public function __construct(mixed $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->all($columns)->all();
    }

    public function find(int|string $id): mixed
    {
        return $this->model->find($id);
    }

    public function create(array $data): mixed
    {
        return $this->model->create($data);
    }
}
