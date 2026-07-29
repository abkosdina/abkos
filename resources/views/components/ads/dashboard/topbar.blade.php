<header class="h-[68px] shrink-0 border-b border-gray-100 bg-white flex items-center justify-between px-5 gap-4">
  <div class="flex items-center gap-3 min-w-0">
    <button @click="sidebarOpen = true" class="lg:hidden p-2 -mr-2 text-ink-700">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="min-w-0">
      <p class="text-[15px] font-extrabold text-ink-900 truncate" x-text="activeGroupLabel"></p>
      <p class="text-[11.5px] text-ink-400 truncate" x-text="activeItem"></p>
    </div>
  </div>

  <div class="flex items-center gap-3 shrink-0">
    <button class="relative p-2 rounded-xl hover:bg-gray-50 transition-colors">
      <svg class="w-5 h-5 text-ink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9"/></svg>
      <span x-show="notificationsCount > 0" class="absolute -top-0.5 -left-0.5 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center" x-text="notificationsCount"></span>
    </button>
    <div class="hidden sm:flex items-center gap-3 pr-3 border-r border-gray-100">
      <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center font-bold text-teal-700 text-[12px]" x-text="user.avatarInitial"></div>
      <div class="min-w-0">
        <p class="text-[13px] font-semibold text-ink-800 truncate" x-text="user.name"></p>
        <p class="text-[11px] text-ink-400 truncate" x-text="user.email"></p>
      </div>
    </div>
  </div>
</header>
