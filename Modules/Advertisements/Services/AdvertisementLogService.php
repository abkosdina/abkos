<?php

namespace Modules\Advertisements\Services;

use Illuminate\Http\Request;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementLogRepositoryInterface;

class AdvertisementLogService
{
    public function __construct(protected AdvertisementLogRepositoryInterface $repository)
    {
    }

    public function log(int|string $advertisementId, int|string|null $userId, string $action, array $oldValues = [], array $newValues = [], ?Request $request = null): object
    {
        return $this->repository->create([
            'advertisement_id' => $advertisementId,
            'user_id' => $userId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip' => $request?->ip(),
            'device' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
