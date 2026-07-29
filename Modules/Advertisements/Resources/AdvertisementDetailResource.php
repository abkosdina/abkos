<?php

namespace Modules\Advertisements\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Modules\Advertisements\Resources\LoanOfferResource;

class AdvertisementDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'advertisement_number' => $this->advertisement_number,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'priority' => $this->priority,
            'seller_id' => $this->seller_user_id,
            'seller' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'rating' => $this->user->rating ?? $this->computeSellerRating(),
            ]),
            'bank_name' => $this->loanOffer?->bank?->name,
            'plan' => $this->loanOffer?->loanPlan?->name,
            'bank_initial' => $this->bankInitial(),
            'loan_amount' => $this->loanOffer?->loan_amount,
            'score_price' => $this->loanOffer?->sale_price,
            'profit_rate' => $this->loanOffer?->interest_rate,
            'repayment_months' => $this->loanOffer?->installment_count,
            'installment_amount' => $this->loanOffer?->monthly_installment,
            'province' => $this->province?->name ?? $this->mapProvinceName($this->province_id),
            'city' => $this->city?->name ?? $this->mapCityName($this->city_id),
            'urgent' => $this->mapUrgent($this->priority),
            'vip' => $this->mapVip($this->loanOffer?->vip_guarantee ?? false),
            'guaranteed' => (bool) ($this->loanOffer?->escrow_enabled ?? false),
            'negotiable' => (bool) ($this->loanOffer?->is_negotiable ?? false),
            'contract_ready' => (bool) ($this->loanOffer?->contract_ready ?? false),
            'full_docs' => false,
            'transaction_type' => $this->mapTransactionType(
                (bool) ($this->loanOffer?->escrow_enabled ?? false),
                (bool) ($this->loanOffer?->vip_guarantee ?? false),
                (bool) ($this->loanOffer?->is_online ?? false),
                (bool) ($this->loanOffer?->is_in_person ?? false),
            ),
            'views' => $this->getViewsCount(),
            'contacts' => $this->contacts_count ?? 0,
            'created_at' => $this->created_at,
            'created_at_jalali' => $this->created_at_jalali,
            'created_at_label' => $this->created_at_label,
            'published_at_jalali' => $this->published_at_jalali,
            'published_at_label' => $this->published_at_label,
            'loan_offer' => $this->when($this->loanOffer, new LoanOfferResource($this->loanOffer)),
        ];
    }

    private function bankInitial(): ?string
    {
        $source = $this->loanOffer?->bank?->name ?? $this->title;
        return $source ? mb_substr(trim($source), 0, 1) : null;
    }

    private function computeSellerRating()
    {
        $sellerId = $this->seller_user_id ?? ($this->user?->id ?? null);
        if (! $sellerId) {
            return null;
        }

        $avg = DB::table('ratings')->where('to_user_id', $sellerId)->avg('score');
        if ($avg === null) {
            return null;
        }

        return round($avg, 1);
    }

    private function getViewsCount(): int
    {
        if ($this->views_count !== null) {
            return $this->views_count;
        }

        if (DB::getSchemaBuilder()->hasTable('advertisement_views')) {
            return DB::table('advertisement_views')->where('advertisement_id', $this->id)->count();
        }

        return 0;
    }

    private function mapProvinceName($id)
    {
        if (! $id) {
            return null;
        }

        $map = [
            1 => 'آذربایجان شرقی',
            2 => 'آذربایجان غربی',
            3 => 'اردبیل',
            4 => 'اصفهان',
            5 => 'البرز',
            6 => 'ایلام',
            7 => 'بوشهر',
            8 => 'تهران',
            9 => 'چهارمحال و بختیاری',
            10 => 'خراسان جنوبی',
            11 => 'خراسان رضوی',
            12 => 'خراسان شمالی',
            13 => 'خوزستان',
            14 => 'زنجان',
            15 => 'سمنان',
            16 => 'سیستان و بلوچستان',
            17 => 'فارس',
            18 => 'قزوین',
            19 => 'قم',
            20 => 'کردستان',
            21 => 'کرمان',
            22 => 'کرمانشاه',
            23 => 'کهگیلویه و بویراحمد',
            24 => 'گلستان',
            25 => 'گیلان',
            26 => 'لرستان',
            27 => 'مازندران',
            28 => 'مرکزی',
            29 => 'هرمزگان',
            30 => 'همدان',
            31 => 'یزد',
        ];

        return $map[$id] ?? $id;
    }

    private function mapCityName($id)
    {
        if (! $id) {
            return null;
        }

        $map = [
            1 => 'تهران',
            2 => 'مشهد',
            3 => 'اصفهان',
            4 => 'شیراز',
            5 => 'تبریز',
            6 => 'کرج',
            7 => 'استانک',
            8 => 'قم',
            9 => 'اهواز',
            10 => 'کرمانشاه',
            11 => 'ارومیه',
            12 => 'یزد',
            13 => 'رشت',
            14 => 'بندرعباس',
            15 => 'ساری',
            16 => 'قائمشهر',
            17 => 'اردبیل',
            18 => 'کرمان',
            19 => 'بجنورد',
            20 => 'زاهدان',
        ];

        return $map[$id] ?? $id;
    }

    private function mapUrgent($priority): bool
    {
        return (int) $priority >= 2;
    }

    private function mapVip($vipGuarantee): bool
    {
        return (bool) $vipGuarantee;
    }

    private function mapTransactionType(bool $escrowEnabled, bool $vipGuarantee, bool $isOnline, bool $isInPerson): string
    {
        if ($escrowEnabled) {
            return 'escrow';
        }

        if ($vipGuarantee) {
            return 'vip_no_escrow';
        }

        if ($isOnline && ! $isInPerson) {
            return 'online';
        }

        if ($isInPerson && ! $isOnline) {
            return 'in_person';
        }

        return 'escrow';
    }
}
