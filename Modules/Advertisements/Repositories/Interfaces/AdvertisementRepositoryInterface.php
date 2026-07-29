<?php

namespace Modules\Advertisements\Repositories\Interfaces;

interface AdvertisementRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id): ?object;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
