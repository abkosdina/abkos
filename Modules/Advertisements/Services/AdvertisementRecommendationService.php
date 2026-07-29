<?php

namespace Modules\Advertisements\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementRepositoryInterface;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementRecommendationService
{
    public function __construct(protected AdvertisementRepositoryInterface $repo)
    {
    }

    /**
     * Recommend advertisements for a user based on weighted scoring and cache the results.
     */
    public function recommendForUser(int $userId, array $params = []): Collection
    {
        $config = config('advertisements.recommendation');
        $ttl = $config['ttl_minutes'] ?? 60;
        $weights = $config['weights'] ?? [];
        $max = $config['max_scan'] ?? 1000;
        $limit = $config['limit'] ?? 50;

        $globalVersion = Cache::get('advertisements:recommendations:version', 1);
        $userVersion = Cache::get("advertisements:recommendations:user_version:{$userId}", 1);
        $cacheKey = "advertisements:recommendations:version:{$globalVersion}:user_version:{$userVersion}:user:{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($weights, $max, $limit) {
            $query = Advertisement::query()
                ->published()
                ->publiclyVisible()
                ->active()
                ->select(['id', 'user_id', 'uuid', 'created_at', 'views_count', 'priority', 'province_id', 'city_id', 'loan_product_id', 'bank_id', 'loan_plan_id'])
                ->limit($max);

            $ads = $query->with('user:id,name,rating')->get();

            $advertisementIds = $ads->pluck('id')->filter()->values();
            $viewCounts = collect();
            $favoriteCounts = collect();
            $negotiationCounts = collect();
            $orderCounts = collect();
            $sellerRatings = collect();

            if ($advertisementIds->isNotEmpty()) {
                if (Schema::hasTable('advertisement_views')) {
                    $viewCounts = DB::table('advertisement_views')
                        ->selectRaw('advertisement_id, COUNT(*) as total')
                        ->whereIn('advertisement_id', $advertisementIds)
                        ->groupBy('advertisement_id')
                        ->pluck('total', 'advertisement_id');
                }

                if (Schema::hasTable('advertisement_favorites')) {
                    $favoriteCounts = DB::table('advertisement_favorites')
                        ->selectRaw('advertisement_uuid, COUNT(*) as total')
                        ->whereIn('advertisement_uuid', $ads->pluck('uuid')->filter()->all())
                        ->groupBy('advertisement_uuid')
                        ->pluck('total', 'advertisement_uuid');
                }

                if (Schema::hasTable('negotiations')) {
                    $negotiationCounts = DB::table('negotiations')
                        ->selectRaw('advertisement_id, COUNT(*) as total')
                        ->whereIn('advertisement_id', $advertisementIds)
                        ->groupBy('advertisement_id')
                        ->pluck('total', 'advertisement_id');
                }

                if (Schema::hasTable('orders')) {
                    $orderCounts = DB::table('orders')
                        ->selectRaw('advertisement_id, COUNT(*) as total')
                        ->whereIn('advertisement_id', $advertisementIds)
                        ->groupBy('advertisement_id')
                        ->pluck('total', 'advertisement_id');
                }

                if (Schema::hasTable('ratings')) {
                    $sellerIds = $ads->pluck('user_id')->filter()->unique()->values();
                    if ($sellerIds->isNotEmpty()) {
                        $sellerRatings = DB::table('ratings')
                            ->selectRaw('to_user_id, AVG(score) as average_score')
                            ->whereIn('to_user_id', $sellerIds)
                            ->groupBy('to_user_id')
                            ->pluck('average_score', 'to_user_id');
                    }
                }
            }

            $scored = $ads->map(function ($a) use ($weights, $viewCounts, $favoriteCounts, $negotiationCounts, $orderCounts, $sellerRatings) {
                $views = $a->views_count ?? (int) ($viewCounts[$a->id] ?? 0);
                $favorites = (int) ($favoriteCounts[$a->uuid] ?? 0);
                $negotiations = (int) ($negotiationCounts[$a->id] ?? 0);
                $orders = (int) ($orderCounts[$a->id] ?? 0);
                $ageDays = $a->created_at ? now()->diffInDays($a->created_at) : 0;
                $sellerRating = $a->user && isset($a->user->rating) ? (float) $a->user->rating : (float) ($sellerRatings[$a->user_id] ?? 0.0);

                $score = 0.0;
                $score += ($weights['views'] ?? 0.0) * log(1 + $views);
                $score += ($weights['favorites'] ?? 0.0) * $favorites;
                $score += ($weights['negotiations'] ?? 0.0) * $negotiations;
                $score += ($weights['orders'] ?? 0.0) * $orders;
                $score += ($weights['age'] ?? 0.0) * (1 / (1 + $ageDays));
                $score += ($weights['seller_rating'] ?? 0.0) * $sellerRating;

                $a->score = $score;
                return $a;
            });

            return $scored->sortByDesc('score')->values()->take($limit);
        });
    }

    public function invalidateUserCache(int $userId): void
    {
        $userKey = "advertisements:recommendations:user_version:{$userId}";
        if (! Cache::has($userKey)) {
            Cache::forever($userKey, 1);
        }
        Cache::increment($userKey);
    }

    /**
     * Recommend advertisements similar to a given advertisement id/uuid
     */
    public function recommendSimilar(Advertisement|int|string $ad, int $limit = 20): Collection
    {
        $base = $ad instanceof Advertisement ? $ad : Advertisement::query()->where('uuid', $ad)->firstOrFail();

        $query = Advertisement::query()->where('status', 'Published')
            ->where('id', '!=', $base->id)
            ->where(function ($q) use ($base) {
                $q->where('bank_id', $base->bank_id)
                  ->orWhere('loan_plan_id', $base->loan_plan_id)
                  ->orWhere('province_id', $base->province_id)
                  ->orWhere('city_id', $base->city_id);
            })
            ->orderByRaw('priority desc, created_at desc')
            ->limit($limit);

        return $query->get();
    }
}
