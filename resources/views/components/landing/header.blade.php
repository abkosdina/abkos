<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
    <div class="max-w-[1180px] mx-auto px-5 h-[76px] flex items-center justify-between">
        <a href="/" class="flex items-center gap-3 shrink-0">
            <img src="{{ asset('cdn/logo.svg') }}" alt="لوگو مستر وام" class="site-logo rounded-2xl border border-teal-100 bg-white/90 shadow-sm" />
            <span class="text-[19px] font-extrabold text-ink-900">مستر وام</span>
        </a>

        <nav class="hidden lg:flex items-center gap-8 text-[14.5px] text-ink-800 font-medium">
            <a href="#" @click.prevent="window.scrollTo({top:0,behavior:'smooth'})" class="hover:text-teal-600 transition-colors">خانه</a>
            <a href="#" @click.prevent="goToLoans()" class="hover:text-teal-600 transition-colors">آگهی ها</a>
            <a href="#" @click.prevent="goToGuide()" class="hover:text-teal-600 transition-colors">راهنما</a>
            <a href="#" @click.prevent="goToAbout()" class="hover:text-teal-600 transition-colors">درباره ما</a>
            <a href="#" @click.prevent="goToContact()" class="hover:text-teal-600 transition-colors">تماس با ما</a>
            <a href="#" @click.prevent="goToBlog()" class="hover:text-teal-600 transition-colors">وبلاگ</a>
        </nav>

        <div class="flex items-center gap-3">
            <div class="hidden lg:flex items-center gap-3">
                <div x-show="!isAuthenticated" x-cloak class="flex items-center gap-3">
                    <button @click="goToRegister()"
                        class="inline-flex text-[14px] font-semibold px-5 py-2.5 rounded-xl border border-gray-300 text-ink-800 hover:border-teal-500 hover:text-teal-600 transition-colors">
                        ثبت نام
                    </button>
                    <button @click="goToLogin()"
                        class="inline-flex text-[14px] font-semibold px-5 py-2.5 rounded-xl border border-gray-300 text-ink-800 hover:border-teal-500 hover:text-teal-600 transition-colors">
                        ورود
                    </button>
                </div>
                <div x-show="isAuthenticated" x-cloak>
                    <button @click="goToDashboard()"
                        class="btn-shine text-[14px] font-semibold px-5 py-2.5 rounded-xl bg-teal-600 text-white hover:bg-teal-700 transition-colors">
                        پنل کاربری
                    </button>
                </div>
            </div>
            <button class="lg:hidden p-2 text-ink-800" @click="mobileMenu = !mobileMenu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg>
            </button>
        </div>

    </div>

    <div x-cloak x-show="mobileMenu" x-transition class="lg:hidden border-t border-gray-100 px-5 py-4 flex flex-col gap-4 text-[14.5px] font-medium">
        <a href="#" @click.prevent="window.scrollTo({top:0,behavior:'smooth'})">خانه</a>
        <a href="#" @click.prevent="goToLoans()">آگهی ها</a>
        <a href="#" @click.prevent="goToGuide()">راهنما</a>
        <a href="#" @click.prevent="goToAbout()">درباره ما</a>
        <a href="#" @click.prevent="goToContact()">تماس با ما</a>
        <a href="#" @click.prevent="goToBlog()">وبلاگ</a>
        <template x-if="!isAuthenticated">
            <button @click="goToRegister()" class="w-full text-right border border-gray-300 rounded-xl py-2.5 font-semibold">ثبت نام</button>
            <button @click="goToLogin()" class="w-full text-right border border-gray-300 rounded-xl py-2.5 font-semibold">ورود</button>
        </template>
        <template x-if="isAuthenticated">
            <button @click="goToDashboard()" class="w-full bg-teal-600 text-white rounded-xl py-2.5 font-semibold">پنل کاربری</button>
        </template>
    </div>
</header>
