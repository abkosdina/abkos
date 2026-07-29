<?php

namespace Modules\UserManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Modules\Advertisements\Models\Advertisement;
use Modules\Negotiation\Models\Negotiation;
use Modules\Shared\Base\BaseController;
use Modules\UserManagement\Models\ActivityLog;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletBalance;
use Illuminate\Support\Str;

class DashboardController extends BaseController
{
    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Config::get('user-management.dashboard', []),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $adsQuery = Advertisement::query()->where('seller_user_id', $user->id);
        $activeAds = (clone $adsQuery)->whereIn('status', ['Published', 'PendingReview', 'Paused'])->count();
        $totalAds = (clone $adsQuery)->count();

        $wallet = Wallet::query()->where('user_id', $user->id)->first();
        $walletBalance = 0;
        if ($wallet) {
            $balance = WalletBalance::query()->where('wallet_id', $wallet->id)->latest('created_at')->first();
            $walletBalance = (float) ($balance?->total_balance ?? $balance?->available_balance ?? 0);
        }

        $openNegotiations = Negotiation::query()->where(function ($query) use ($user) {
            $query->where('buyer_id', $user->id)
                ->orWhere('seller_id', $user->id);
        })->whereIn('status', ['Open', 'Pending', 'Accepted'])->count();

        $unreadMessages = 0;

        $stats = [
            [
                'label' => 'آگهی‌های فعال',
                'value' => number_format($activeAds),
                'bg' => 'bg-teal-50 text-teal-600',
                'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>',
            ],
            [
                'label' => 'موجودی کیف پول',
                'value' => number_format((int) $walletBalance) . (str_contains((string) $walletBalance, '.') ? '' : ''),
                'bg' => 'bg-emerald-50 text-emerald-600',
                'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2"/></svg>',
            ],
            [
                'label' => 'امتیاز من',
                'value' => number_format((int) ($user->score ?? 0)),
                'bg' => 'bg-amber-50 text-amber-600',
                'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93a1 1 0 011.9 0l1.36 2.76 3.04.44a1 1 0 01.56 1.7l-2.2 2.15.52 3.03a1 1 0 01-1.45 1.05L10 12.6l-2.72 1.43a1 1 0 01-1.45-1.05l.52-3.03-2.2-2.15a1 1 0 01.56-1.7l3.04-.44 1.36-2.76z"/></svg>',
            ],
            [
                'label' => 'مذاکرات باز',
                'value' => number_format($openNegotiations),
                'bg' => 'bg-sky-50 text-sky-600',
                'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-1"/></svg>',
            ],
            [
                'label' => 'سفارش‌های در جریان',
                'value' => '0',
                'bg' => 'bg-purple-50 text-purple-600',
                'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
            ],
            [
                'label' => 'پیام‌های خوانده‌نشده',
                'value' => number_format($unreadMessages),
                'bg' => 'bg-rose-50 text-rose-600',
                'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            ],
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function activity(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $logs = ActivityLog::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('causer_id', $user->id);
            })
            ->latest('created_at')
            ->limit(8)
            ->get();

        $items = $logs->map(function (ActivityLog $log) {
            $message = match ($log->event) {
                'advertisement.created' => 'آگهی جدید در سیستم ثبت شد.',
                'advertisement.submitted' => 'آگهی برای بررسی ارسال شد.',
                'advertisement.approved' => 'آگهی تأیید شد.',
                default => 'رویداد جدید ثبت شد.',
            };

            return [
                'text' => $message,
                'time' => $log->created_at?->diffForHumans() ?? 'به تازگی',
                'bg' => 'bg-teal-50 text-teal-600',
                'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>',
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $items]);
    }
}
