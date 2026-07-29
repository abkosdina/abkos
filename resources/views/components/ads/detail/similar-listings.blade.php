<div class="mt-16">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-[19px] font-extrabold text-ink-900">آگهی‌های دیگر همین فروشنده</h2>
    <p class="text-[13px] text-ink-400" x-show="!loadingSeller" x-text="listing?.seller.name"></p>
  </div>

  <div x-cloak x-show="loadingSeller" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-5">
    <template x-for="i in 5" :key="i">
      <div class="border border-gray-100 rounded-2xl p-5 h-[210px] skeleton"></div>
    </template>
  </div>

  <div x-cloak x-show="!loadingSeller && sellerListings.length === 0" class="text-center py-10 border border-dashed border-gray-200 rounded-2xl text-[13.5px] text-ink-400">
    آگهی دیگری از این فروشنده یافت نشد
  </div>

  <div x-cloak x-show="!loadingSeller && sellerListings.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-5 stagger">
    <template x-for="item in sellerListings" :key="item.id">
      <div class="card-hover border border-gray-100 rounded-2xl p-5 bg-white">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-[13px]" :class="item.color" x-text="item.bankInitial"></div>
        <p class="mt-3.5 font-bold text-[13.5px] text-ink-900" x-text="item.title"></p>
        <span class="inline-block mt-2 text-[11px] font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full" x-text="item.repaymentMonths + ' ماهه · سود ' + item.profitRate + '٪'"></span>
        <p class="mt-3 font-extrabold text-[14px] text-ink-900" x-text="formatToman(item.scorePrice) + ' تومان'"></p>
        <p class="text-[11.5px] text-ink-400 mt-1" x-text="item.city"></p>
        <button @click="goToListing(item.id)" class="mt-3 w-full text-center text-[12.5px] font-semibold py-2 rounded-xl border border-gray-200 hover:border-teal-500 hover:text-teal-700 transition-colors">
          مشاهده جزئیات
        </button>
      </div>
    </template>
  </div>
</div>
