<section id="listings" class="max-w-[1180px] mx-auto px-5 py-20">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between mb-10">
        <div>
            <p class="text-[13px] font-semibold text-teal-600">آگهی‌های اخیر</p>
            <h2 class="text-[24px] sm:text-[28px] font-extrabold text-ink-900">امتیازهای وام با بهترین شرایط</h2>
        </div>
        <a href="/ads/loadLoans" class="inline-flex items-center justify-center rounded-full border border-gray-200 px-4 py-2 text-[13px] font-semibold text-ink-700 hover:border-teal-500 hover:text-teal-600 transition-colors">
            مشاهده همه آگهی‌ها
        </a>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <template x-if="loadingListings">
            <div class="space-y-5 md:col-span-2 xl:col-span-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm animate-pulse h-60"></div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm animate-pulse h-60"></div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm animate-pulse h-60"></div>
            </div>
        </template>

        <template x-if="!loadingListings && listings.length === 0">
            <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-ink-500">
                هیچ آگهی پیشنهادی ویژه‌ای پیدا نشد.
            </div>
        </template>

        <template x-for="item in listings" :key="item.id">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl text-sm font-bold text-white" :class="item.color">
                        <span x-text="item.bankInitial"></span>
                    </div>
                    <span class="rounded-full bg-teal-50 px-2.5 py-1 text-[11px] font-bold text-teal-700">پیشنهاد ویژه</span>
                </div>

                <h3 class="mt-4 text-[15px] font-extrabold text-ink-900" x-text="item.title"></h3>
                <p class="mt-1 text-[12.5px] text-ink-500" x-text="item.plan"></p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full bg-gray-50 px-2.5 py-1 text-[11px] font-bold text-ink-600" x-text="item.score + ' امتیاز'"></span>
                    <span class="rounded-full bg-gray-50 px-2.5 py-1 text-[11px] font-bold text-ink-600" x-text="item.city"></span>
                </div>

                <p class="mt-4 text-[15px] font-extrabold text-ink-900" x-text="formatToman(item.price) + ' تومان'"></p>

                <a :href="'/ads/detail?id=' + item.id" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-gray-200 py-2.5 text-[13px] font-semibold text-ink-700 transition hover:border-teal-500 hover:text-teal-700">
                    مشاهده جزئیات
                </a>
            </div>
        </template>
    </div>
</section>
