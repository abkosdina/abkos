<?php

namespace Modules\Advertisements\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Enums\AdvertisementVisibility;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementDiscoveryService
{
    public function __construct(protected AdvertisementQueryBuilder $builder)
    {
    }

    public function search(array $params = []): array
    {
        $perPage = (int) ($params['per_page'] ?? 15);

        $qb = $this->builder->forRequest($params)->withEager(['user', 'loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city']);
        $qb->applySort($params['sort'] ?? null);

        $page = $qb->paginate($perPage);

        return ['data' => $page, 'meta' => [
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
        ]];
    }

    public function findByUuid(string $uuid): ?Advertisement
    {
        $advertisement = Advertisement::query()->where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        if ($advertisement->status !== AdvertisementStatus::Published
            || $advertisement->visibility !== AdvertisementVisibility::Public
        ) {
            if (! $user || ($advertisement->user_id !== $user->id && ! $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']))) {
                abort(404);
            }
        }

        return $advertisement;
    }

    public function findById(int $id): ?Advertisement
    {
        return Advertisement::query()->findOrFail($id);
    }

    public function availableFilters(): array
    {
        // Build master data and dynamic ranges from DB (cached)
        return Cache::remember('advertisements:filters', 60, function () {
            $filters = ['statuses' => [], 'visibilities' => [], 'provinces' => [], 'cities' => [], 'priorities' => []];

            if (\Illuminate\Support\Facades\Schema::hasTable('advertisements')) {
                $filters['statuses'] = Advertisement::query()->select('status')->distinct()->pluck('status')->toArray();
                $filters['visibilities'] = Advertisement::query()->select('visibility')->distinct()->pluck('visibility')->toArray();
                $filters['provinces'] = Advertisement::query()->select('province_id')->distinct()->pluck('province_id')->filter()->values()->toArray();
                $filters['cities'] = Advertisement::query()->select('city_id')->distinct()->pluck('city_id')->filter()->values()->toArray();
                $priorities = Advertisement::query()->select('priority')->distinct()->pluck('priority')->filter()->unique()->values()->toArray();

                // map numeric priorities to human labels
                $map = [
                    0 => 'معمولی',
                    1 => 'VIP',
                    2 => 'فوری',
                    3 => 'اورژانسی',
                ];

                $filters['priorities'] = collect($priorities)->sort()->reverse()->map(fn($p) => ['value' => (int) $p, 'label' => $map[(int)$p] ?? (string)$p])->values()->toArray();
            }

            return $filters;
        });
    }

    public function similar(string $uuid, array $params = [])
    {
        $ad = $this->findByUuid($uuid);

        $qb = clone $this->builder;
        $qb = $qb->forRequest([
            'province_id' => $ad->province_id,
            'city_id' => $ad->city_id,
            'loan_amount_min' => $ad->loan_amount ?? null,
        ])->withEager(['user', 'loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city']);

        return $qb->where('id', '!=', $ad->id)->limit(20)->get();
    }

    public function recommended(array $params = [])
    {
        // simple: return latest vip and highest priority
        $qb = clone $this->builder;
        $qb = $qb->forRequest($params)->withEager(['user', 'loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city']);

        return $qb->applySort('priority:desc')->limit(50)->get();
    }

    public function popular(array $params = [])
    {
        $qb = clone $this->builder;
        $qb = $qb->forRequest($params)->withEager(['user', 'loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city']);

        return $qb->applySort('views_count:desc')->limit(50)->get();
    }

    public function latest(array $params = [])
    {
        $qb = clone $this->builder;
        $qb = $qb->forRequest($params)->withEager(['user', 'loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city']);

        return $qb->applySort('created_at:desc')->limit(50)->get();
    }

    public function trending(array $params = [])
    {
        // naive trending by views in last period
        return $this->popular($params);
    }

    public function recentlyViewed(?int $userId = null)
    {
        // placeholder: query recently viewed table if exists
        if (! \Illuminate\Support\Facades\Schema::hasTable('advertisement_views')) {
            return collect();
        }

        $q = \Illuminate\Support\Facades\DB::table('advertisement_views')->whereNotNull('advertisement_id');
        if ($userId) {
            $q->where('user_id', $userId);
        }

        $ids = $q->orderBy('created_at', 'desc')->limit(50)->pluck('advertisement_id')->toArray();

        return Advertisement::query()->whereIn('id', $ids)->get();
    }
}
