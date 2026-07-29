<div class="space-y-5">
  <div class="border border-gray-100 rounded-2xl p-6">
    <div class="flex items-center gap-2 mb-3">
      <span x-show="listing?.urgent" class="text-[11px] font-bold bg-red-50 text-red-500 px-2.5 py-1 rounded-full">فوری</span>
      <span x-show="listing?.vip" class="text-[11px] font-bold bg-amber-50 text-amber-600 px-2.5 py-1 rounded-full">VIP</span>
      <span x-show="listing?.guaranteed" class="text-[11px] font-bold bg-teal-50 text-teal-700 px-2.5 py-1 rounded-full">ضمانت سایت</span>
    </div>

    <p class="text-[13px] text-ink-400">قیمت فروش امتیاز</p>
    <p class="text-[28px] font-extrabold text-teal-700 mt-1" x-text="formatToman(listing?.scorePrice) + ' تومان'"></p>

    <div class="grid grid-cols-2 gap-3 mt-5 text-center">
      <div class="bg-gray-50 rounded-xl p-3">
        <p class="text-[13px] font-extrabold text-ink-900" x-text="formatToman(listing?.loanAmount)"></p>
        <p class="text-[11px] text-ink-400 mt-0.5">مبلغ وام (تومان)</p>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <p class="text-[13px] font-extrabold text-ink-900" x-text="listing?.profitRate + '٪'"></p>
        <p class="text-[11px] text-ink-400 mt-0.5">نرخ سود</p>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <p class="text-[13px] font-extrabold text-ink-900" x-text="listing?.repaymentMonths + ' ماهه'"></p>
        <p class="text-[11px] text-ink-400 mt-0.5">مدت بازپرداخت</p>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <p class="text-[13px] font-extrabold text-ink-900" x-text="formatToman(listing?.installmentAmount)"></p>
        <p class="text-[11px] text-ink-400 mt-0.5">مبلغ هر قسط</p>
      </div>
    </div>

    <button @click="startNegotiation()" class="btn-shine w-full mt-6 bg-teal-600 text-white font-bold py-3.5 rounded-xl hover:bg-teal-700 transition-colors shadow-lg shadow-teal-600/25">
      آغاز مذاکره
    </button>
    <div class="flex gap-2 mt-2.5">
      <button class="flex-1 border border-gray-200 rounded-xl py-2.5 text-[13px] font-semibold hover:border-teal-500 transition-colors flex items-center justify-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"/></svg>
        ذخیره
      </button>
      <button class="flex-1 border border-gray-200 rounded-xl py-2.5 text-[13px] font-semibold hover:border-teal-500 transition-colors flex items-center justify-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.68 13.34a3 3 0 100-2.68m0 2.68a3 3 0 110-2.68m0 2.68l6.64 3.98m-6.64-6.66l6.64-3.98m0 0a3 3 0 105.02-2.22 3 3 0 00-5.02 2.22zm0 10.64a3 3 0 105.02 2.22 3 3 0 00-5.02-2.22z"/></svg>
        اشتراک
      </button>
    </div>
  </div>

  @includeIf('components.ads.detail.seller-card')
</div>
