<?php

namespace Modules\Advertisements\Services;

use Modules\Advertisements\Models\Advertisement;

class AdvertisementLimitService
{
    public function countActiveAdvertisements(int|string $userId): int
    {
        return Advertisement::query()
            ->ownedBy($userId)
            ->active()
            ->select('id')
            ->limit($this->getActiveLimit())
            ->get()
            ->count();
    }

    public function countDailyCreatedAdvertisements(int|string $userId): int
    {
        return Advertisement::query()
            ->where('seller_user_id', $userId)
            ->createdOn()
            ->select('id')
            ->limit($this->getDailyCreationLimit())
            ->get()
            ->count();
    }

    public function getActiveLimit(): int
    {
        return (int) config('advertisements.limits.active_per_user', 10);
    }

    public function getDailyCreationLimit(): int
    {
        return (int) config('advertisements.limits.daily_creation_per_user', 5);
    }

    public function ensureCanCreate(int|string $userId): void
    {
        $dailyLimit = $this->getDailyCreationLimit();
        $activeLimit = $this->getActiveLimit();

        $todayCount = $this->countDailyCreatedAdvertisements($userId);
        if ($todayCount >= $dailyLimit) {
            throw new \RuntimeException(sprintf('Daily advertisement creation limit reached (%d per day).', $dailyLimit));
        }

        $activeCount = $this->countActiveAdvertisements($userId);
        if ($activeCount >= $activeLimit) {
            throw new \RuntimeException(sprintf('Active advertisement limit reached (%d active ads).', $activeLimit));
        }
    }
}
