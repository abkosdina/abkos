<?php

namespace Modules\Advertisements\Services;

use Modules\Advertisements\DTO\LoanOfferDTO;
use Modules\Advertisements\Repositories\Interfaces\LoanOfferRepositoryInterface;

class LoanOfferService
{
    public function __construct(protected LoanOfferRepositoryInterface $repository)
    {
    }

    public function create(int|string $advertisementId, LoanOfferDTO $dto): object
    {
        $data = $dto->toArray();
        $data['advertisement_id'] = $advertisementId;

        return $this->repository->create($data);
    }
}
