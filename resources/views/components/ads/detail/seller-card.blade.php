<div class="border border-gray-100 rounded-2xl p-6">
  <p class="font-bold text-[14px] text-ink-900 mb-4">اطلاعات فروشنده</p>
  <div class="flex items-center gap-3">
    <div class="w-11 h-11 rounded-full bg-teal-100 flex items-center justify-center font-bold text-teal-700 text-[14px]" x-text="listing?.seller.name[0]"></div>
    <div>
      <p class="text-[13.5px] font-bold text-ink-900" x-text="listing?.seller.name"></p>
      <p class="text-[11.5px] text-ink-400" x-text="'عضویت از ' + (listing?.seller?.joinedAt ?? 'سال پیش')"></p>
    </div>
  </div>
  <div class="grid grid-cols-2 gap-2 mt-4 text-[12px]">
    <div class="flex items-center gap-1.5 text-ink-600">
      <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93a1 1 0 011.9 0l1.36 2.76 3.04.44a1 1 0 01.56 1.7l-2.2 2.15.52 3.03a1 1 0 01-1.45 1.05L10 12.6l-2.72 1.43a1 1 0 01-1.45-1.05l.52-3.03-2.2-2.15a1 1 0 01.56-1.7l3.04-.44 1.36-2.76z"/></svg>
      <span x-text="listing?.seller.rating + ' امتیاز'"></span>
    </div>
    <div class="flex items-center gap-1.5 text-ink-600">
      <svg class="w-4 h-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span x-text="listing?.seller.verified ? 'احراز هویت شده' : 'در انتظار احراز'"></span>
    </div>
    <div class="flex items-center gap-1.5 text-ink-600">
      <svg class="w-4 h-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
      <span x-text="(listing?.seller?.activeAdsCount ?? 0) + ' آگهی فعال'"></span>
    </div>
    <div class="flex items-center gap-1.5 text-ink-600" x-show="listing?.seller.vip">
      <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <span>فروشنده VIP</span>
    </div>
  </div>
</div>
