<?php

namespace Modules\Advertisements\Repositories\Eloquent;

use Modules\Advertisements\Repositories\Interfaces\AdvertisementFavoriteRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AdvertisementFavoriteRepository implements AdvertisementFavoriteRepositoryInterface
{
    public function favorite(int $userId, string $advertisementUuid): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('advertisement_favorites')) {
            return false;
        }

        return (bool) DB::table('advertisement_favorites')->insertGetId([
            'user_id' => $userId,
            'advertisement_uuid' => $advertisementUuid,
            'created_at' => now(),
        ]);
    }

    public function unfavorite(int $userId, string $advertisementUuid): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('advertisement_favorites')) {
            return false;
        }

        return (bool) DB::table('advertisement_favorites')->where('user_id', $userId)->where('advertisement_uuid', $advertisementUuid)->delete();
    }

    public function listForUser(int $userId, int $perPage = 20)
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('advertisement_favorites')) {
            return collect();
        }

        $uuids = DB::table('advertisement_favorites')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($perPage)
            ->pluck('advertisement_uuid')
            ->filter()
            ->values()
            ->toArray();

        if (empty($uuids)) {
            return collect();
        }

        return \Modules\Advertisements\Models\Advertisement::query()
            ->whereIn('uuid', $uuids)
            ->select(['id', 'uuid', 'title', 'status', 'visibility', 'seller_user_id', 'created_at'])
            ->get();
    }
}
