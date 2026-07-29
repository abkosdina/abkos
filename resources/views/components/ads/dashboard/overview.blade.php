<template x-if="activeItem === 'نمای کلی'">
  <div>
    <div class="mb-6 animate-fadeUp">
      <h1 class="text-[21px] sm:text-[24px] font-extrabold text-ink-900">سلام، <span x-text="user.name"></span> 👋</h1>
      <p class="text-[13.5px] text-ink-400 mt-1">خلاصه‌ای از وضعیت حساب و فعالیت‌های اخیر شما</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-7 stagger">
      <template x-for="action in quickActions" :key="action.id">
        <button @click="selectItem(action.group, action.item)" class="card-hover rounded-2xl p-4 text-right transition-colors" :class="action.button_class">
          <div x-html="action.icon"></div>
          <p class="text-[12.5px] font-bold" :class="action.text_class" x-text="action.label"></p>
        </button>
      </template>
    </div>

    <div x-cloak x-show="loadingStats" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-7">
      <template x-for="i in 6" :key="i"><div class="h-[92px] rounded-2xl skeleton"></div></template>
    </div>
    <div x-cloak x-show="!loadingStats" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-7 stagger">
      <template x-for="s in resolvedStats" :key="s.key">
        <div class="card-hover bg-white border border-gray-100 rounded-2xl p-4">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-2" :class="s.bg" x-html="s.icon"></div>
          <p class="text-[15px] font-extrabold text-ink-900" x-text="s.value"></p>
          <p class="text-[11px] text-ink-400 mt-0.5" x-text="s.label"></p>
        </div>
      </template>
    </div>

    <div x-show="isSuperAdmin" class="bg-white border border-gray-100 rounded-2xl p-5 mb-6">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="font-bold text-[15px] text-ink-900">عضویت اربران</p>
          <p class="text-[12px] text-ink-400 mt-1">در این بخش می‌توانید ثبت‌نام اربران را فعال یا غیرفعال کنید.</p>
        </div>
        <button @click="toggleBrokerRegistration()" :disabled="togglingBrokerRegistration" class="rounded-xl px-3 py-2 text-sm font-semibold text-white transition" :class="brokerRegistrationEnabled ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'" x-text="togglingBrokerRegistration ? 'در حال اعمال...' : (brokerRegistrationEnabled ? 'غیرفعال‌سازی عضویت' : 'فعال‌سازی عضویت')"></button>
      </div>
      <div class="mt-3 flex items-center gap-2 text-sm" :class="brokerRegistrationEnabled ? 'text-emerald-600' : 'text-rose-600'">
        <span class="inline-block h-2.5 w-2.5 rounded-full" :class="brokerRegistrationEnabled ? 'bg-emerald-500' : 'bg-rose-500'"></span>
        <span x-text="brokerRegistrationEnabled ? 'ثبت‌نام اربران فعلاً فعال است.' : 'ثبت‌نام اربران غیرفعال است و کاربر با پیام مناسب متوقف می‌شود.'"></span>
      </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-5">
      <p class="font-bold text-[15px] text-ink-900 mb-4">فعالیت‌های اخیر</p>

      <div x-cloak x-show="loadingActivity" class="space-y-3">
        <template x-for="i in 4" :key="i"><div class="h-[52px] rounded-xl skeleton"></div></template>
      </div>

      <div x-cloak x-show="!loadingActivity" class="space-y-1 stagger">
        <template x-for="(act, idx) in activity" :key="idx">
          <div class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0" :class="act.bg" x-html="act.icon"></div>
            <div class="min-w-0 flex-1">
              <p class="text-[13px] text-ink-800" x-text="act.text"></p>
              <p class="text-[11px] text-ink-400 mt-0.5" x-text="act.time"></p>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
