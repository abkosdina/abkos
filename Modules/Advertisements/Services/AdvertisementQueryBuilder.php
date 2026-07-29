<?php

namespace Modules\Advertisements\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Enums\AdvertisementVisibility;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementQueryBuilder
{
    protected bool $joinedLoanOffers = false;
    protected bool $joinedBanks = false;
    protected bool $joinedBankPlans = false;

    public function __construct(protected ?Builder $query = null)
    {
        $this->query = $this->query ?? Advertisement::query();
    }

    public function forRequest(Request|array $request): self
    {
        $data = is_array($request) ? $request : $request->all();

        $hasStatusFilter = array_key_exists('status', $data) || array_key_exists('ad_status', $data);
        $hasVisibilityFilter = array_key_exists('visibility', $data);

        if (! $hasStatusFilter) {
            $this->query->where('status', AdvertisementStatus::Published->value);
        }

        if (! $hasVisibilityFilter) {
            $this->query->where('visibility', AdvertisementVisibility::Public->value);
        }

        // Full-text-ish search fields
        if (! empty($data['q'])) {
            $q = $data['q'];
            $this->query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // Filtering by enum/relations via dynamic keys
        foreach (['visibility', 'status'] as $key) {
            if (isset($data[$key])) {
                $this->query->where($key, $data[$key]);
            }
        }

        $this->applyOptionalFilter('user_id', $data);
        $this->applyOptionalFilter('seller_user_id', $data);

        // Price filters (works directly on advertisements table)
        if (isset($data['score_price_min']) || isset($data['sale_price_min'])) {
            $min = $data['score_price_min'] ?? $data['sale_price_min'];
            if ($min) {
                $this->query->where('price', '>=', $min);
            }
        }

        if (isset($data['score_price_max']) || isset($data['sale_price_max'])) {
            $max = $data['score_price_max'] ?? $data['sale_price_max'];
            if ($max) {
                $this->query->where('price', '<=', $max);
            }
        }

        // Location filters
        foreach (['province_id', 'city_id'] as $loc) {
            if (isset($data[$loc]) && $data[$loc]) {
                $this->query->where($loc, $data[$loc]);
            }
        }

        if (! empty($data['ad_status'])) {
            $statuses = $this->normalizeArray($data['ad_status']);
            $this->query->where(function ($qb) use ($statuses) {
                foreach ($statuses as $status) {
                    $status = trim((string) $status);
                    if ($status === 'active') {
                        $qb->orWhere('status', 'Published');
                    } elseif ($status === 'unsold') {
                        $qb->orWhere('status', '!=', 'Sold');
                    } elseif ($status === 'negotiating' && Schema::hasTable('negotiations')) {
                        $qb->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))->from('negotiations')
                                ->whereColumn('negotiations.advertisement_id', 'advertisements.id');
                        });
                    } elseif ($status === 'urgent') {
                        $qb->orWhere('priority', '>=', 2);
                    } elseif ($status === 'vip') {
                        $qb->orWhereHas('loanOffer', fn ($q) => $q->where('vip_guarantee', true));
                    } elseif ($status === 'guaranteed') {
                        $qb->orWhereHas('loanOffer', fn ($q) => $q->where('escrow_enabled', true));
                    }
                }
            });
        }

        if (! empty($data['transaction_type'])) {
            $types = $this->normalizeArray($data['transaction_type']);
            $this->query->whereHas('loanOffer', function ($q) use ($types) {
                $q->where(function ($qb) use ($types) {
                    foreach ($types as $type) {
                        if ($type === 'escrow') {
                            $qb->orWhere('escrow_enabled', true);
                        } elseif ($type === 'vip_no_escrow') {
                            $qb->orWhere(function ($sub) {
                                $sub->where('vip_guarantee', true)->where('escrow_enabled', false);
                            });
                        } elseif ($type === 'online') {
                            $qb->orWhere(function ($sub) {
                                $sub->where('is_online', true)->where('is_in_person', false);
                            });
                        } elseif ($type === 'in_person') {
                            $qb->orWhere(function ($sub) {
                                $sub->where('is_in_person', true)->where('is_online', false);
                            });
                        }
                    }
                });
            });
        }

        if (! empty($data['seller_type'])) {
            $types = $this->normalizeArray($data['seller_type']);
            $this->query->where(function ($qb) use ($types) {
                foreach ($types as $type) {
                    if ($type === 'verified') {
                        $qb->orWhereHas('user', fn ($userQuery) => $userQuery->whereNotNull('email_verified_at'));
                    } elseif ($type === 'vip') {
                        if (Schema::hasTable('vip_memberships')) {
                            $qb->orWhereHas('user', function ($userQuery) {
                                $userQuery->whereExists(function ($sub) {
                                    $sub->select(DB::raw(1))->from('vip_memberships')
                                        ->whereColumn('vip_memberships.user_id', 'users.id')
                                        ->where('vip_memberships.status', 'active')
                                        ->where(function ($active) {
                                            $active->whereNull('vip_memberships.ends_at')->orWhere('vip_memberships.ends_at', '>=', now());
                                        });
                                });
                            });
                        } else {
                            $qb->orWhereHas('loanOffer', fn ($offerQuery) => $offerQuery->where('vip_guarantee', true));
                        }
                    } elseif ($type === 'featured') {
                        $qb->orWhere('priority', '>=', 1);
                    } elseif ($type === 'high_rating') {
                        if (Schema::hasColumn('users', 'rating')) {
                            $qb->orWhereHas('user', fn ($userQuery) => $userQuery->where('rating', '>=', 4));
                        }
                    }
                }
            });
        }

        if (! empty($data['popularity'])) {
            $types = $this->normalizeArray($data['popularity']);
            $this->query->where(function ($qb) use ($types) {
                foreach ($types as $type) {
                    if ($type === 'most_viewed' && Schema::hasColumn('advertisements', 'views_count')) {
                        $avg = (int) DB::table('advertisements')->avg('views_count');
                        $qb->orWhere('views_count', '>=', max(1, $avg));
                    } elseif ($type === 'most_contacted' && Schema::hasColumn('advertisements', 'contacts_count')) {
                        $avg = (int) DB::table('advertisements')->avg('contacts_count');
                        $qb->orWhere('contacts_count', '>=', max(1, $avg));
                    } elseif ($type === 'most_negotiated' && Schema::hasTable('negotiations')) {
                        $qb->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))->from('negotiations')
                                ->whereColumn('negotiations.advertisement_id', 'advertisements.id');
                        });
                    } elseif ($type === 'most_sold') {
                        $qb->orWhere('status', 'Sold');
                    }
                }
            });
        }

        if (! empty($data['registered_time'])) {
            $ranges = $this->normalizeArray($data['registered_time']);
            $this->query->where(function ($qb) use ($ranges) {
                foreach ($ranges as $range) {
                    $range = trim((string) $range);
                    if ($range === 'today') {
                        $qb->orWhere('created_at', '>=', now()->startOfDay());
                    } elseif ($range === 'yesterday') {
                        $qb->orWhereBetween('created_at', [now()->subDay()->startOfDay(), now()->startOfDay()]);
                    } elseif ($range === 'this_week') {
                        $qb->orWhere('created_at', '>=', now()->startOfWeek());
                    } elseif ($range === 'this_month') {
                        $qb->orWhere('created_at', '>=', now()->startOfMonth());
                    }
                }
            });
        }

        if (! empty($data['contract_status'])) {
            $statuses = $this->normalizeArray($data['contract_status']);
            $this->query->whereHas('loanOffer', function ($q) use ($statuses) {
                $q->where(function ($qb) use ($statuses) {
                    foreach ($statuses as $status) {
                        if ($status === 'contract_ready') {
                            $qb->orWhere('contract_ready', true);
                        } elseif ($status === 'needs_contract') {
                            $qb->orWhere('contract_ready', false);
                        }
                    }
                });
            });
        }

        if (! empty($data['negotiation_status'])) {
            $statuses = $this->normalizeArray($data['negotiation_status']);
            $this->query->whereHas('loanOffer', function ($q) use ($statuses) {
                $q->where(function ($qb) use ($statuses) {
                    foreach ($statuses as $status) {
                        if ($status === 'negotiable') {
                            $qb->orWhere('is_negotiable', true);
                        } elseif ($status === 'fixed_price') {
                            $qb->orWhere('is_negotiable', false);
                        }
                    }
                });
            });
        }

        if (! empty($data['documents'])) {
            $types = $this->normalizeArray($data['documents']);
            $this->query->where(function ($qb) use ($types) {
                foreach ($types as $type) {
                    if ($type === 'full_verification') {
                        $qb->orWhereHas('user', fn ($userQuery) => $userQuery->whereNotNull('email_verified_at'));
                    } elseif ($type === 'esign' && Schema::hasTable('user_signatures')) {
                        $qb->orWhereHas('user', function ($userQuery) {
                            $userQuery->whereExists(function ($sub) {
                                $sub->select(DB::raw(1))->from('user_signatures')
                                    ->whereColumn('user_signatures.user_id', 'users.id')
                                    ->whereNotNull('signed_at');
                            });
                        });
                    } elseif ($type === 'full_docs') {
                        $qb->orWhereHas('documents');
                    }
                }
            });
        }

        // Banks / loan-offer related filters
        $hasLoanOfferFilter = ! empty($data['banks']) || ! empty($data['plan']) || isset($data['loan_amount_min']) || isset($data['loan_amount_max'])
            || isset($data['profit_min']) || isset($data['profit_max'])
            || isset($data['repayment_months_min']) || isset($data['repayment_months_max'])
            || isset($data['installment_min']) || isset($data['installment_max'])
            || isset($data['affordable_installment_min']) || isset($data['affordable_installment_max']);

        if ($hasLoanOfferFilter) {
            $this->query->whereHas('loanOffer', function ($q) use ($data) {
                if (! empty($data['banks'])) {
                    $banks = is_array($data['banks']) ? $data['banks'] : explode(',', (string) $data['banks']);
                    $bankCodes = array_filter($banks, fn ($bank) => ! ctype_digit((string) $bank));
                    $bankIds = array_filter($banks, fn ($bank) => ctype_digit((string) $bank));

                    if (! empty($bankCodes) && Schema::hasTable('banks')) {
                        $q->whereIn('bank_id', function ($sub) use ($bankCodes) {
                            $sub->select('id')->from('banks')->whereIn('code', array_values($bankCodes));
                        });
                    }

                    if (! empty($bankIds)) {
                        $q->orWhereIn('bank_id', array_values($bankIds));
                    }
                }

                if (! empty($data['plan'])) {
                    if (Schema::hasTable('bank_loan_products')) {
                        $q->whereIn('loan_plan_id', function ($sub) use ($data) {
                            $sub->select('id')->from('bank_loan_products')->where('name', $data['plan']);
                        });
                    } elseif (Schema::hasTable('loan_products')) {
                        $q->whereIn('loan_plan_id', function ($sub) use ($data) {
                            $sub->select('id')->from('loan_products')->where('name', $data['plan']);
                        });
                    }
                }

                if (isset($data['loan_amount_min']) && $data['loan_amount_min'] !== '') {
                    $q->where('loan_amount', '>=', $data['loan_amount_min']);
                }
                if (isset($data['loan_amount_max']) && $data['loan_amount_max'] !== '') {
                    $q->where('loan_amount', '<=', $data['loan_amount_max']);
                }

                if (isset($data['profit_min']) && $data['profit_min'] !== '') {
                    $q->where('interest_rate', '>=', $data['profit_min']);
                }
                if (isset($data['profit_max']) && $data['profit_max'] !== '') {
                    $q->where('interest_rate', '<=', $data['profit_max']);
                }

                if (isset($data['repayment_months_min']) && $data['repayment_months_min'] !== '') {
                    $q->where('installment_count', '>=', $data['repayment_months_min']);
                }
                if (isset($data['repayment_months_max']) && $data['repayment_months_max'] !== '') {
                    $q->where('installment_count', '<=', $data['repayment_months_max']);
                }

                if (isset($data['installment_min']) && $data['installment_min'] !== '') {
                    $q->where('monthly_installment', '>=', $data['installment_min']);
                }
                if (isset($data['installment_max']) && $data['installment_max'] !== '') {
                    $q->where('monthly_installment', '<=', $data['installment_max']);
                }

                if (isset($data['affordable_installment_min']) && $data['affordable_installment_min'] !== '') {
                    $q->where('monthly_installment', '>=', $data['affordable_installment_min']);
                }
                if (isset($data['affordable_installment_max']) && $data['affordable_installment_max'] !== '') {
                    $q->where('monthly_installment', '<=', $data['affordable_installment_max']);
                }
            });
        }

        // max buyer payment maps to advertisement price (score price)
        if (isset($data['max_buyer_payment'])) {
            $max = $data['max_buyer_payment'];
            if ($max !== null && $max !== '') {
                $this->query->where('advertisements.price', '<=', $max);
            }
        }

        // seller rating minimum: join users and check rating if provided
        if (isset($data['seller_rating_min'])) {
            $min = $data['seller_rating_min'];
            if ($min !== null && $min !== '' && Schema::hasColumn('users', 'rating')) {
                $this->joinUsers();
                $this->query->where('users.rating', '>=', $min);
            }
        }

        return $this;
    }

    public function applySort(string|array|null $sort = null): self
    {
        $sort = $sort ?? request('sort');
        if (empty($sort)) {
            $this->query->latest('created_at');
            return $this;
        }

        if (is_string($sort)) {
            if (! str_contains($sort, ':')) {
                $sort = $this->normalizeSort($sort);
            }

            if (empty($sort)) {
                $this->query->latest('created_at');
                return $this;
            }

            $parts = explode(':', $sort);
            $col = $parts[0];
            $dir = $parts[1] ?? 'desc';

            if (in_array($col, ['loan_amount', 'sale_price', 'interest_rate', 'monthly_installment'], true)) {
                $this->joinLoanOffers();
                $col = "loan_offers.{$col}";
            }

            if ($col === 'users.rating' || str_ends_with($col, '.rating')) {
                if (Schema::hasColumn('users', 'rating')) {
                    $this->joinUsers();
                } else {
                    $col = 'created_at';
                    $dir = 'desc';
                }
            }

            $this->query->orderBy($col, $dir);
        }

        return $this;
    }

    protected function normalizeSort(string $sort): ?string
    {
        return match ($sort) {
            'newest' => 'created_at:desc',
            'oldest' => 'created_at:asc',
            'cheapest' => 'price:asc',
            'expensive' => 'price:desc',
            'highest_loan' => 'loan_amount:desc',
            'lowest_profit' => 'interest_rate:asc',
            'top_seller_rating' => 'users.rating:desc',
            'most_viewed' => 'views_count:desc',
            'most_popular' => 'views_count:desc',
            'nearest_city' => 'city_id:asc',
            'featured' => 'published_at:desc',
            default => 'created_at:desc',
        };
    }

    protected function shouldJoinLoanOffers(array $data): bool
    {
        // Check if filters that would require loan_offers table are present
        // Since this query builder uses relationship-based filtering, joins are not required here.
        return false;
    }

    protected function normalizeArray(string|array|null $values): array
    {
        if (is_array($values)) {
            return array_values(array_filter(array_map(fn ($value) => $value !== null ? trim((string) $value) : null, $values), fn ($value) => $value !== null && $value !== ''));
        }

        if ($values === null || $values === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $values)), fn ($value) => $value !== ''));
    }

    protected function applyOptionalFilter(string $key, array $data): void
    {
        if (isset($data[$key]) && $data[$key]) {
            $this->query->where($key, $data[$key]);
        }
    }

    protected function joinLoanOffers(): void
    {
        if ($this->joinedLoanOffers) {
            return;
        }

        $this->query->leftJoin('loan_offers', 'advertisements.id', '=', 'loan_offers.advertisement_id')
                    ->select('advertisements.*');

        $this->joinedLoanOffers = true;
    }

    protected function joinBanks(): void
    {
        if ($this->joinedBanks) {
            return;
        }

        $this->joinLoanOffers();
        $this->query->leftJoin('banks', 'banks.id', '=', 'loan_offers.bank_id');
        $this->joinedBanks = true;
    }

    protected function joinBankPlans(): void
    {
        if ($this->joinedBankPlans) {
            return;
        }

        $this->joinLoanOffers();
        $this->query->leftJoin('bank_loan_products', 'bank_loan_products.id', '=', 'loan_offers.loan_plan_id');
        $this->joinedBankPlans = true;
    }

    protected function joinUsers(): void
    {
        // join users table to allow filtering by seller rating or other user attributes
        // ensure advertisements.* remains selected
        if (strpos($this->query->toSql(), 'join users') !== false) {
            return;
        }

        $this->query->leftJoin('users', 'users.id', '=', 'advertisements.seller_user_id')
                    ->select('advertisements.*');
    }

    public function withEager(array $with = []): self
    {
        if (! empty($with)) {
            $this->query->with($with);
        }

        return $this;
    }

    public function paginate(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->query->paginate($perPage);
    }

    public function get(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query->get();
    }
}
