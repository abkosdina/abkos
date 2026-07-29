<div>
  <div class="flex items-start gap-3">
    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white font-bold text-[16px] shrink-0" :class="listing?.color" x-text="listing?.bankInitial"></div>
    <div>
      <h1 class="text-[20px] sm:text-[23px] font-extrabold text-ink-900" x-text="listing?.title"></h1>
      <p class="text-[13px] text-ink-400 mt-1" x-text="listing?.plan + ' · ' + listing?.city + '، ' + listing?.province"></p>
    </div>
  </div>

  <!-- description -->
  <div class="mt-6 border-t border-gray-100 pt-6">
    <p class="font-bold text-[15px] text-ink-900 mb-2">توضیحات آگهی</p>
    <p class="text-[13.5px] text-ink-600 leading-8" x-text="listing?.description"></p>
  </div>

  <!-- full details grid -->
  <div class="mt-6 border-t border-gray-100 pt-6">
    <p class="font-bold text-[15px] text-ink-900 mb-4">مشخصات کامل وام</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <template x-for="row in detailRows" :key="row.label">
        <div class="bg-gray-50 rounded-xl p-3.5">
          <p class="text-[11.5px] text-ink-400" x-text="row.label"></p>
          <p class="text-[13.5px] font-bold text-ink-900 mt-1" x-text="row.value"></p>
        </div>
      </template>
    </div>
  </div>

  <!-- status & tags -->
  <div class="mt-6 border-t border-gray-100 pt-6">
    <p class="font-bold text-[15px] text-ink-900 mb-3">وضعیت آگهی</p>
    <div class="flex flex-wrap gap-2">
      <span class="text-[12px] font-semibold px-3 py-1.5 rounded-full bg-teal-50 text-teal-700" x-text="listing?.negotiable ? 'قابل مذاکره' : 'قیمت قطعی'"></span>
      <span class="text-[12px] font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-ink-700" x-text="listing?.contractReady ? 'قرارداد آماده' : 'نیازمند قرارداد'"></span>
      <span class="text-[12px] font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-ink-700" x-text="transactionTypeLabel(listing?.transactionType)"></span>
      <span class="text-[12px] font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-ink-700" x-show="listing?.fullDocs">مدارک کامل</span>
    </div>
  </div>

  <!-- stats -->
  <div class="mt-6 border-t border-gray-100 pt-6 flex flex-wrap gap-6 text-[13px] text-ink-400">
    <span class="flex items-center gap-1.5">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      <span x-text="listing?.views + ' بازدید'"></span>
    </span>
    <span class="flex items-center gap-1.5">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-1"/></svg>
      <span x-text="listing?.contacts + ' تماس'"></span>
    </span>
    <span x-text="'تاریخ ثبت: ' + listing?.createdAtLabel"></span>
  </div>
</div>
