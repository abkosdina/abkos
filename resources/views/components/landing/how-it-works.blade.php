<section class="max-w-[1180px] mx-auto px-5 py-20">
    <div class="text-center mb-14">
        <h2 class="text-[26px] font-extrabold text-ink-900 animate-fadeUp">چگونه کار می‌کند؟</h2>
        <p class="text-ink-400 text-[14px] mt-2 animate-fadeUp" style="animation-delay:.1s">فرآیند خرید و فروش امتیاز وام در مستر وام</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-5 gap-6 relative stagger">
        <div class="hidden sm:block absolute top-7 right-[10%] left-[10%] border-t-2 border-dashed border-teal-200 -z-10"></div>
        <template x-for="step in steps" :key="step.n">
            <div class="text-center">
                <div class="relative mx-auto w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center mb-4">
                    <span x-html="step.icon" class="text-teal-600"></span>
                    <span class="absolute -top-2 -left-2 w-6 h-6 rounded-full bg-ink-900 text-white text-[11px] font-bold flex items-center justify-center" x-text="step.n"></span>
                </div>
                <p class="font-bold text-[15px] text-ink-900" x-text="step.title"></p>
                <p class="text-[12.5px] text-ink-400 mt-1.5 leading-6" x-text="step.desc"></p>
            </div>
        </template>
    </div>
</section>
