<?php

namespace Modules\Shared\Services;

use App\Models\Province;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    public function getProvinces(?string $search = null): Collection
    {
        $cacheKey = $search ? sprintf('locations:provinces:%s', $search) : 'locations:provinces';

        return Cache::remember($cacheKey, 60, function () use ($search): Collection {
            return Province::query()
                ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%"))
                ->withCount('cities')
                ->orderBy('name')
                ->get();
        });
    }

    public function getCitiesByProvince(int|string|null $provinceId, ?string $search = null): Collection
    {
        if (! $provinceId) {
            return collect();
        }

        $cacheKey = sprintf('locations:province:%s:cities:%s', $provinceId, $search ?? '');

        return Cache::remember($cacheKey, 60, function () use ($provinceId, $search): Collection {
            $province = Province::find($provinceId);
            if (! $province) {
                return collect();
            }

            return $province->cities()
                ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%"))
                ->select('id', 'province_id', 'name', 'name_en', 'is_capital')
                ->orderByDesc('is_capital')
                ->orderBy('name')
                ->get();
        });
    }
}
