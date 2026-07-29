<section class="border-y border-gray-100 bg-gray-50/60">
    <div class="max-w-[1180px] mx-auto px-5 py-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-center stagger">
        <div>
            <p class="text-[26px] font-extrabold text-ink-900" x-text="counters.ads.toLocaleString('en-US') + '+'"></p>
            <p class="text-[13px] text-ink-400 mt-1">آگهی فعال</p>
        </div>
        <div>
            <p class="text-[26px] font-extrabold text-ink-900" x-text="counters.deals.toLocaleString('en-US') + '+'"></p>
            <p class="text-[13px] text-ink-400 mt-1">معامله موفق</p>
        </div>
        <div>
            <p class="text-[26px] font-extrabold text-ink-900" x-text="counters.banks + '+'"></p>
            <p class="text-[13px] text-ink-400 mt-1">بانک های طرف قرارداد</p>
        </div>
        <div>
            <p class="text-[26px] font-extrabold text-teal-600" x-text="counters.volume.toLocaleString('en-US') + ' میلیارد'"></p>
            <p class="text-[13px] text-ink-400 mt-1">ارزش امتیازهای معامله شده</p>
        </div>
    </div>
</section>
