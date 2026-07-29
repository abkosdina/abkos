<?php

namespace Modules\Advertisements\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AdvertisementListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'published_at' => $this->published_at?->toIso8601String(),
            'published_at_jalali' => $this->published_at_jalali,
            'published_at_label' => $this->published_at_label,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'expires_at_jalali' => $this->expires_at_jalali,
            'expires_at_label' => $this->expires_at_label,
            'plan' => $this->loanOffer?->loanPlan?->name,
            'bank_name' => $this->loanOffer?->bank?->name,

            // location: return human-readable names (use relations when loaded)
            'province' => $this->province?->name ?? $this->mapProvinceName($this->province_id),
            'city' => $this->city?->name ?? $this->mapCityName($this->city_id),

            // loan offer details
            'loan_amount' => $this->loanOffer?->loan_amount ?? null,
            'installment_count' => $this->loanOffer?->installment_count ?? null,
            'interest_rate' => $this->loanOffer?->interest_rate ?? null,

            // seller info (include rating if present on user)
            'seller' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'rating' => $this->user->rating ?? $this->computeSellerRating(),
            ]),
            'priority' => $this->priority,
            'urgent' => $this->mapUrgent($this->priority),
            'vip' => $this->mapVip($this->loanOffer?->vip_guarantee ?? false),
            'priority_label' => $this->mapPriorityLabel($this->priority, $this->loanOffer?->vip_guarantee ?? false),
        ];
    }

    private function mapProvinceName($id)
    {
        if (! $id) {
            return null;
        }

        return $this->lookupName($id, [
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
        ]);
    }

    private function mapCityName($id)
    {
        if (! $id) {
            return null;
        }

        return $this->lookupName($id, [
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
        ]);
    }

    private function lookupName($id, array $map)
    {
        return $map[$id] ?? $id;
    }

    private function computeSellerRating()
    {
        $sellerId = $this->seller_user_id ?? ($this->user?->id ?? null);
        if (! $sellerId) return null;

        $avg = DB::table('ratings')->where('to_user_id', $sellerId)->avg('score');
        if ($avg === null) return null;
        return round($avg, 1);
    }

    private function mapUrgent($priority): bool
    {
        return (int) $priority >= 2;
    }

    private function mapVip($vipGuarantee): bool
    {
        return (bool) $vipGuarantee;
    }

    private function mapPriorityLabel($priority, $vipGuarantee = false)
    {
        $isVip = (bool) $vipGuarantee;
        $isUrgent = (int) $priority >= 2;

        if ($isVip && $isUrgent) {
            return 'VIP · فوری';
        }

        if ($isVip) {
            return 'VIP';
        }

        if ($isUrgent) {
            return 'فوری';
        }

        $map = [
            0 => 'معمولی',
            1 => 'VIP',
            2 => 'فوری',
            3 => 'فوری',
        ];

        return $map[(int) $priority] ?? (string) $priority;
    }
}
