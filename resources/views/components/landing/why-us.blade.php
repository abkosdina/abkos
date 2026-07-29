<section class="max-w-[1180px] mx-auto px-5 py-6">
    <div class="bg-gradient-to-l from-teal-800 to-teal-900 rounded-3xl px-8 py-12 text-white">
        <h2 class="text-center text-[24px] font-extrabold mb-10 animate-fadeUp">چرا مستر وام؟</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 stagger">
            <template x-for="f in whyUs" :key="f.title">
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-white/10 flex items-center justify-center mb-3" x-html="f.icon"></div>
                    <p class="font-bold text-[14px]" x-text="f.title"></p>
                    <p class="text-[11.5px] text-teal-100/70 mt-1 leading-5" x-text="f.desc"></p>
                </div>
            </template>
        </div>
    </div>
</section>
