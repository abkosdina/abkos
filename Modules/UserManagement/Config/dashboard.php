<?php

return [
    'quick_actions' => [
        [
            'id' => 'create_ad',
            'label' => 'ثبت آگهی جدید',
            'group' => 'ads',
            'item' => 'ثبت آگهی جدید',
            'icon' => '<svg class="w-5 h-5 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>',
            'bg' => 'bg-teal-600 text-white',
            'text_class' => 'text-white',
            'button_class' => 'card-hover bg-teal-600 text-white rounded-2xl p-4 text-right hover:bg-teal-700 transition-colors',
        ],
        [
            'id' => 'kyc',
            'label' => 'احراز هویت',
            'group' => 'kyc',
            'item' => 'وضعیت احراز هویت',
            'icon' => '<svg class="w-5 h-5 mb-2 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'bg' => 'bg-white border border-gray-100',
            'text_class' => 'text-ink-800',
            'button_class' => 'card-hover bg-white border border-gray-100 rounded-2xl p-4 text-right',
        ],
        [
            'id' => 'wallet',
            'label' => 'شارژ کیف پول',
            'group' => 'wallet',
            'item' => 'واریز',
            'icon' => '<svg class="w-5 h-5 mb-2 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2"/></svg>',
            'bg' => 'bg-white border border-gray-100',
            'text_class' => 'text-ink-800',
            'button_class' => 'card-hover bg-white border border-gray-100 rounded-2xl p-4 text-right',
        ],
        [
            'id' => 'support',
            'label' => 'پشتیبانی',
            'group' => 'support',
            'item' => 'ثبت تیکت',
            'icon' => '<svg class="w-5 h-5 mb-2 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
            'bg' => 'bg-white border border-gray-100',
            'text_class' => 'text-ink-800',
            'button_class' => 'card-hover bg-white border border-gray-100 rounded-2xl p-4 text-right',
        ],
    ],
    'stats' => [
        [
            'key' => 'active_ads',
            'label' => 'آگهی‌های فعال',
            'bg' => 'bg-teal-50 text-teal-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>',
        ],
        [
            'key' => 'wallet_balance',
            'label' => 'موجودی کیف پول',
            'bg' => 'bg-emerald-50 text-emerald-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2"/></svg>',
        ],
        [
            'key' => 'score',
            'label' => 'امتیاز من',
            'bg' => 'bg-amber-50 text-amber-600',
            'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93a1 1 0 011.9 0l1.36 2.76 3.04.44a1 1 0 01.56 1.7l-2.2 2.15.52 3.03a1 1 0 01-1.45 1.05L10 12.6l-2.72 1.43a1 1 0 01-1.45-1.05l.52-3.03-2.2-2.15a1 1 0 01.56-1.7l3.04-.44 1.36-2.76z"/></svg>',
        ],
        [
            'key' => 'open_negotiations',
            'label' => 'مذاکرات باز',
            'bg' => 'bg-sky-50 text-sky-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-1"/></svg>',
        ],
        [
            'key' => 'orders_in_progress',
            'label' => 'سفارش‌های در جریان',
            'bg' => 'bg-purple-50 text-purple-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        ],
        [
            'key' => 'unread_messages',
            'label' => 'پیام‌های خوانده‌نشده',
            'bg' => 'bg-rose-50 text-rose-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
    ],
    'sections' => [
        [
            'id' => 'broker_registration',
            'title' => 'عضویت اربران',
            'description' => 'در این بخش می‌توانید ثبت‌نام اربران را فعال یا غیرفعال کنید.',
            'enabled_for_roles' => ['Super Admin'],
        ],
        [
            'id' => 'recent_activity',
            'title' => 'فعالیت‌های اخیر',
            'description' => 'آخرین رویدادهای مرتبط با حساب شما',
            'enabled_for_roles' => ['*'],
        ],
    ],
];
