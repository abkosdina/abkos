<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>
<aside class="fixed lg:static inset-y-0 right-0 w-[280px] shrink-0 bg-white border-l border-gray-100 z-50 flex flex-col transition-transform duration-300 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full'">
  <div class="flex items-center justify-between px-5 h-[68px] shrink-0 border-b border-gray-100">
    <a href="/" class="flex items-center gap-3">
      <img src="{{ asset('cdn/logo.svg') }}" alt="لوگو مستر وام" class="site-logo rounded-2xl border border-teal-100 bg-white/90 shadow-sm" />
      <span class="text-[16.5px] font-extrabold text-ink-900">مستر وام</span>
    </a>
    <button @click="sidebarOpen = false" class="lg:hidden p-1 text-ink-400">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>

  <div class="p-4 border-b border-gray-100 shrink-0">
    <div x-cloak x-show="loadingUser" class="h-[110px] rounded-2xl skeleton"></div>
    <div x-cloak x-show="!loadingUser" class="bg-gradient-to-br from-teal-700 to-teal-900 rounded-2xl p-4 text-white relative overflow-hidden">
      <div class="absolute -left-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
      <div class="flex items-center gap-3 relative">
        <div class="w-12 h-12 rounded-full bg-white/15 flex items-center justify-center font-bold text-[16px] shrink-0" x-text="user.avatarInitial"></div>
        <div class="min-w-0">
          <p class="font-bold text-[14px] truncate" x-text="user.name"></p>
          <p class="text-[11px] text-white/70 truncate" x-text="user.email"></p>
          <div class="flex items-center gap-1 mt-1 flex-wrap">
            <span x-show="user.verified" class="text-[10px] font-bold bg-white/20 px-1.5 py-0.5 rounded-full flex items-center gap-1">
              <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
              احراز شده
            </span>
            <span x-show="user.vip" class="text-[10px] font-bold bg-amber-400/90 text-amber-950 px-1.5 py-0.5 rounded-full">VIP</span>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2 mt-3.5 relative">
        <div class="bg-white/10 rounded-xl px-2.5 py-2">
          <p class="text-[10.5px] text-teal-100/80 flex items-center gap-1">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93a1 1 0 011.9 0l1.36 2.76 3.04.44a1 1 0 01.56 1.7l-2.2 2.15.52 3.03a1 1 0 01-1.45 1.05L10 12.6l-2.72 1.43a1 1 0 01-1.45-1.05l.52-3.03-2.2-2.15a1 1 0 01.56-1.7l3.04-.44 1.36-2.76z"/></svg>
            امتیاز من
          </p>
          <p class="font-extrabold text-[13.5px] mt-0.5" x-text="user.score"></p>
        </div>
        <div class="bg-white/10 rounded-xl px-2.5 py-2">
          <p class="text-[10.5px] text-teal-100/80">موجودی کیف پول</p>
          <p class="font-extrabold text-[12px] mt-0.5" x-text="formatToman(user.walletBalance)"></p>
        </div>
      </div>
    </div>
  </div>

  <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-3">
    <template x-for="group in navGroups" :key="group.id">
      <div class="mb-0.5">
        <button type="button" @click="toggleGroup(group.id)" class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl transition-colors" :class="isGroupActive(group.id) ? 'bg-teal-50 text-teal-700' : 'text-ink-700 hover:bg-gray-50'">
          <span class="flex items-center gap-2 text-[13px] font-semibold">
            <span x-text="group.icon"></span>
            <span x-text="group.label"></span>
          </span>
          <svg class="w-3.5 h-3.5 shrink-0 transition-transform" :class="openGroups[group.id] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div x-show="openGroups[group.id]" x-transition class="pr-9 pl-2 py-0.5 space-y-0.5">
          <template x-for="item in group.items" :key="item">
            <a href="#" @click.prevent="selectItem(group.id, item)" class="block text-[12.5px] py-1.5 px-2.5 rounded-lg transition-colors" :class="activeItem === item ? 'text-teal-700 font-bold bg-teal-50' : 'text-ink-500 hover:bg-gray-50 hover:text-ink-800'" x-text="item"></a>
          </template>
        </div>
      </div>
    </template>
  </nav>

  <div class="p-3 border-t border-gray-100 shrink-0">
    <button @click="logout()" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-[13px] font-semibold text-red-500 hover:bg-red-50 transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V6a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      خروج از حساب
    </button>
  </div>
</aside>
