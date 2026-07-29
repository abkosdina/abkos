<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
    <div class="max-w-[1180px] mx-auto px-5 h-[76px] flex items-center justify-between">
        <a href="/" class="flex items-center gap-3 shrink-0">
            <img src="{{ asset('cdn/logo.svg') }}" alt="لوگو مستر وام" class="site-logo rounded-2xl border border-teal-100 bg-white/90 shadow-sm" />
            <span class="text-[19px] font-extrabold text-ink-900">مستر وام</span>
        </a>

        <nav class="hidden lg:flex items-center gap-8 text-[14.5px] text-ink-800 font-medium">
            <a href="/" class="hover:text-teal-600 transition-colors">خانه</a>
            <a href="/ads/loadLoans" class="hover:text-teal-600 transition-colors">آگهی ها</a>
        </nav>

        <div class="flex items-center gap-3">
            <a href="/users/login"
                class="btn-shine text-[14px] font-semibold px-5 py-2.5 rounded-xl bg-ink-900 text-white hover:bg-teal-700 transition-colors">
                ورود
            </a>
            <button class="lg:hidden p-2 text-ink-800">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>
</header>
