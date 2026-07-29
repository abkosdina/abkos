<?php

namespace Modules\Advertisements\Repositories\Interfaces;

interface LoanOfferRepositoryInterface
{
    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;
}
