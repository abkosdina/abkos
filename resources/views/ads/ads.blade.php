<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مشاهده همه آگهی های امتیاز وام | مستر وام</title>

<link rel="stylesheet" href="<?= asset('cdn/Vazirmatn-font-face.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])

<style>
  html { scroll-behavior: smooth; }
  body { font-family: 'Vazirmatn', sans-serif; }
  [x-cloak] { display: none !important; }

  .stagger > * { animation: fadeUp .6s cubic-bezier(.22,1,.36,1) both; }
  .stagger > *:nth-child(1){animation-delay:.03s} .stagger > *:nth-child(2){animation-delay:.07s}
  .stagger > *:nth-child(3){animation-delay:.11s} .stagger > *:nth-child(4){animation-delay:.15s}
  .stagger > *:nth-child(5){animation-delay:.19s} .stagger > *:nth-child(6){animation-delay:.23s}
  .stagger > *:nth-child(7){animation-delay:.27s} .stagger > *:nth-child(8){animation-delay:.31s}

  .card-hover { transition: transform .3s cubic-bezier(.22,1,.36,1), box-shadow .3s ease; }
  .card-hover:hover { transform: translateY(-6px); box-shadow: 0 20px 40px -18px rgba(15,156,140,.3); }

  .font-quicksand { font-family: 'Quicksand', 'Vazirmatn', sans-serif; }

  .skeleton { background: linear-gradient(90deg,#eef4f3 25%,#f7fbfa 37%,#eef4f3 63%); background-size:400px 100%; animation: shimmer 1.4s infinite linear; }

  ::-webkit-scrollbar { width: 7px; }
  ::-webkit-scrollbar-thumb { background: #cdeee8; border-radius: 8px; }

  
  .slider-wrap { position: relative; height: 22px; direction: ltr; }
  .slider-track { position:absolute; top:8px; right:0; left:0; height:5px; background:#e5e9ef; border-radius:9999px; }
  .slider-fill { position:absolute; top:8px; height:5px; background:#0f9c8c; border-radius:9999px; }
  .range-thumb {
    position:absolute; top:0; right:0; left:0; width:100%; height:22px;
    -webkit-appearance:none; appearance:none; background:transparent; margin:0; pointer-events:none;
  }
  .range-thumb::-webkit-slider-thumb {
    -webkit-appearance:none; pointer-events:auto; width:16px; height:16px; border-radius:50%;
    background:#0f9c8c; border:3px solid #fff; box-shadow:0 1px 4px rgba(15,40,35,.35); cursor:pointer; margin-top:2px;
  }
  .range-thumb::-moz-range-thumb {
    pointer-events:auto; width:16px; height:16px; border-radius:50%; background:#0f9c8c;
    border:3px solid #fff; box-shadow:0 1px 4px rgba(15,40,35,.35); cursor:pointer;
  }
  .range-thumb::-moz-range-track { background: transparent; }

  .chip { transition: all .2s ease; }
  .chip.active { background:#0f9c8c; color:#fff; border-color:#0f9c8c; }

  details.filter-group summary::-webkit-details-marker { display:none; }
</style>
</head>

<body class="bg-white text-ink-900 antialiased" x-data="listingsApp()" x-init="init && init()">

<?= view('components.landing.header')->render(); ?>

<section class="max-w-[1180px] mx-auto px-5 pt-8 pb-4">
  <h1 class="text-[24px] sm:text-[28px] font-extrabold text-ink-900 animate-fadeUp">مشاهده همه آگهی های امتیاز وام</h1>
  <p class="text-ink-400 text-[13.5px] mt-1.5 animate-fadeUp" style="animation-delay:.08s">
    <span x-text="pagination.total.toLocaleString('en-US')"></span> آگهی مطابق با جستجوی شما یافت شد
  </p>
</section>

<section class="max-w-[1180px] mx-auto px-5 pb-2">
  <div class="flex gap-2 overflow-x-auto pb-3 -mx-1 px-1" style="scrollbar-width:none;">
    <template x-for="chip in quickChips" :key="chip.key">
      <button type="button" @click="toggleQuickChip(chip)"
        class="chip shrink-0 whitespace-nowrap text-[12.5px] font-semibold px-3.5 py-2 rounded-full border border-gray-200 text-ink-700 hover:border-teal-400"
        :class="isQuickChipActive(chip) ? 'active' : ''">
        <span x-text="chip.label"></span>
      </button>
    </template>
  </div>
</section>

<section class="max-w-[1180px] mx-auto px-5 pb-16">
  <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-8 items-start">

    
    <button type="button" @click="mobileFilters = true" class="lg:hidden flex items-center justify-center gap-2 w-full border border-gray-200 rounded-xl py-3 font-semibold text-[14px]">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M6.5 12h11M10 18h4"/></svg>
      فیلترها
    </button>

    
    <aside
      class="lg:sticky lg:top-24 bg-white border border-gray-100 rounded-2xl p-5 lg:max-h-[calc(100vh-7rem)] overflow-y-auto
             fixed lg:static inset-0 z-[60] lg:z-auto lg:translate-x-0 transition-transform duration-300"
      :class="mobileFilters ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
      x-cloak x-bind:style="window.innerWidth < 1024 ? 'display:' + (mobileFilters ? 'block' : 'none') : 'display:block'"
    >
      <div class="flex items-center justify-between mb-4 lg:hidden">
        <p class="font-extrabold text-[16px]">فیلترها</p>
        <button @click="mobileFilters = false" class="p-1.5"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </div>

      
      <details class="filter-group border-b border-gray-100 pb-4 mb-4" open>
        <summary class="cursor-pointer list-none flex items-center justify-between font-bold text-[14px] text-ink-900">
          <span>🏛 بانک و طرح وام</span>
          <svg class="w-4 h-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-3 max-h-40 overflow-y-auto space-y-2 pl-1">
          <template x-for="bank in banksList" :key="bank.id">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="bank.id" x-model="filters.banks" @change="onBankFilterChange()" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="bank.name"></span>
            </label>
          </template>
        </div>

        <div class="mt-4">
          <p class="text-[12.5px] font-semibold text-ink-600 mb-2">📋 طرح وام</p>
          <select x-model="filters.plan" :disabled="!hasAvailablePlans"
            class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 disabled:bg-gray-50 disabled:text-ink-400 focus:outline-none focus:border-teal-500">
            <option value="" x-text="hasAvailablePlans ? 'همه طرح‌ها' : 'ابتدا بانک را انتخاب کنید'"></option>
            <template x-for="plan in availablePlans" :key="plan">
              <option :value="plan" x-text="plan"></option>
            </template>
          </select>
        </div>
      </details>

      
      <div class="border-b border-gray-100 pb-4 mb-4">
        <p class="font-bold text-[14px] text-ink-900 mb-3">💵 مبلغ وام (تومان)</p>
        <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
          <span x-text="formatToman(filters.loanAmount.min)"></span>
          <span x-text="formatToman(filters.loanAmount.max)"></span>
        </div>
        <div class="slider-wrap">
          <div class="slider-track"></div>
          <div class="slider-fill" :style="`right:${pct(filters.loanAmount.min, bounds.loanAmount)}%; left:${100 - pct(filters.loanAmount.max, bounds.loanAmount)}%`"></div>
          <input type="range" class="range-thumb" :min="bounds.loanAmount.min" :max="bounds.loanAmount.max" step="10000000"
            x-model.number="filters.loanAmount.min" @input="if(filters.loanAmount.min > filters.loanAmount.max) filters.loanAmount.min = filters.loanAmount.max">
          <input type="range" class="range-thumb" :min="bounds.loanAmount.min" :max="bounds.loanAmount.max" step="10000000"
            x-model.number="filters.loanAmount.max" @input="if(filters.loanAmount.max < filters.loanAmount.min) filters.loanAmount.max = filters.loanAmount.min">
        </div>
      </div>

      
      <div class="border-b border-gray-100 pb-4 mb-4">
        <p class="font-bold text-[14px] text-ink-900 mb-3">💸 قیمت فروش امتیاز (تومان)</p>
        <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
          <span x-text="formatToman(filters.scorePrice.min)"></span>
          <span x-text="formatToman(filters.scorePrice.max)"></span>
        </div>
        <div class="slider-wrap">
          <div class="slider-track"></div>
          <div class="slider-fill" :style="`right:${pct(filters.scorePrice.min, bounds.scorePrice)}%; left:${100 - pct(filters.scorePrice.max, bounds.scorePrice)}%`"></div>
          <input type="range" class="range-thumb" :min="bounds.scorePrice.min" :max="bounds.scorePrice.max" step="1000000"
            x-model.number="filters.scorePrice.min" @input="if(filters.scorePrice.min > filters.scorePrice.max) filters.scorePrice.min = filters.scorePrice.max">
          <input type="range" class="range-thumb" :min="bounds.scorePrice.min" :max="bounds.scorePrice.max" step="1000000"
            x-model.number="filters.scorePrice.max" @input="if(filters.scorePrice.max < filters.scorePrice.min) filters.scorePrice.max = filters.scorePrice.min">
        </div>
      </div>

      
      <div class="border-b border-gray-100 pb-4 mb-4">
        <p class="font-bold text-[14px] text-ink-900 mb-3">📈 نرخ سود (٪)</p>
        <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
          <span x-text="filters.profitRate.min + '٪'"></span>
          <span x-text="filters.profitRate.max + '٪'"></span>
        </div>
        <div class="slider-wrap">
          <div class="slider-track"></div>
          <div class="slider-fill" :style="`right:${pct(filters.profitRate.min, bounds.profitRate)}%; left:${100 - pct(filters.profitRate.max, bounds.profitRate)}%`"></div>
          <input type="range" class="range-thumb" :min="bounds.profitRate.min" :max="bounds.profitRate.max" step="1"
            x-model.number="filters.profitRate.min" @input="if(filters.profitRate.min > filters.profitRate.max) filters.profitRate.min = filters.profitRate.max">
          <input type="range" class="range-thumb" :min="bounds.profitRate.min" :max="bounds.profitRate.max" step="1"
            x-model.number="filters.profitRate.max" @input="if(filters.profitRate.max < filters.profitRate.min) filters.profitRate.max = filters.profitRate.min">
        </div>
      </div>

      
      <div class="border-b border-gray-100 pb-4 mb-4">
        <p class="font-bold text-[14px] text-ink-900 mb-3">📅 مدت بازپرداخت (ماه)</p>
        <div class="flex flex-wrap gap-2 mb-3">
          <template x-for="m in [10,12,18,24,36,48,60]" :key="m">
            <button type="button" @click="filters.repaymentMonths.min = m; filters.repaymentMonths.max = m"
              class="chip text-[12px] font-semibold px-3 py-1.5 rounded-lg border border-gray-200"
              :class="(filters.repaymentMonths.min === m && filters.repaymentMonths.max === m) ? 'active' : ''"
              x-text="m + ' ماهه'"></button>
          </template>
        </div>
        <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
          <span x-text="filters.repaymentMonths.min + ' ماه'"></span>
          <span x-text="filters.repaymentMonths.max + ' ماه'"></span>
        </div>
        <div class="slider-wrap">
          <div class="slider-track"></div>
          <div class="slider-fill" :style="`right:${pct(filters.repaymentMonths.min, bounds.repaymentMonths)}%; left:${100 - pct(filters.repaymentMonths.max, bounds.repaymentMonths)}%`"></div>
          <input type="range" class="range-thumb" :min="bounds.repaymentMonths.min" :max="bounds.repaymentMonths.max" step="1"
            x-model.number="filters.repaymentMonths.min" @input="if(filters.repaymentMonths.min > filters.repaymentMonths.max) filters.repaymentMonths.min = filters.repaymentMonths.max">
          <input type="range" class="range-thumb" :min="bounds.repaymentMonths.min" :max="bounds.repaymentMonths.max" step="1"
            x-model.number="filters.repaymentMonths.max" @input="if(filters.repaymentMonths.max < filters.repaymentMonths.min) filters.repaymentMonths.max = filters.repaymentMonths.min">
        </div>
      </div>

      
      <div class="border-b border-gray-100 pb-4 mb-4">
        <p class="font-bold text-[14px] text-ink-900 mb-3">🧾 مبلغ هر قسط (تومان)</p>
        <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
          <span x-text="formatToman(filters.installmentAmount.min)"></span>
          <span x-text="formatToman(filters.installmentAmount.max)"></span>
        </div>
        <div class="slider-wrap">
          <div class="slider-track"></div>
          <div class="slider-fill" :style="`right:${pct(filters.installmentAmount.min, bounds.installmentAmount)}%; left:${100 - pct(filters.installmentAmount.max, bounds.installmentAmount)}%`"></div>
          <input type="range" class="range-thumb" :min="bounds.installmentAmount.min" :max="bounds.installmentAmount.max" step="500000"
            x-model.number="filters.installmentAmount.min" @input="if(filters.installmentAmount.min > filters.installmentAmount.max) filters.installmentAmount.min = filters.installmentAmount.max">
          <input type="range" class="range-thumb" :min="bounds.installmentAmount.min" :max="bounds.installmentAmount.max" step="500000"
            x-model.number="filters.installmentAmount.max" @input="if(filters.installmentAmount.max < filters.installmentAmount.min) filters.installmentAmount.max = filters.installmentAmount.min">
        </div>
      </div>

      
      <div class="border-b border-gray-100 pb-4 mb-4 bg-teal-50/60 -mx-5 px-5 pt-4 rounded-none">
        <p class="font-bold text-[14px] text-ink-900 mb-1">💚 توان پرداخت قسط ماهانه من</p>
        <p class="text-[11.5px] text-ink-500 mb-3">آگهی‌هایی که با بودجه ماهانه شما سازگارند نمایش داده می‌شوند</p>
        <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
          <span x-text="formatToman(filters.affordableInstallment.min)"></span>
          <span x-text="formatToman(filters.affordableInstallment.max)"></span>
        </div>
        <div class="slider-wrap">
          <div class="slider-track"></div>
          <div class="slider-fill" :style="`right:${pct(filters.affordableInstallment.min, bounds.affordableInstallment)}%; left:${100 - pct(filters.affordableInstallment.max, bounds.affordableInstallment)}%`"></div>
          <input type="range" class="range-thumb" :min="bounds.affordableInstallment.min" :max="bounds.affordableInstallment.max" step="500000"
            x-model.number="filters.affordableInstallment.min" @input="if(filters.affordableInstallment.min > filters.affordableInstallment.max) filters.affordableInstallment.min = filters.affordableInstallment.max">
          <input type="range" class="range-thumb" :min="bounds.affordableInstallment.min" :max="bounds.affordableInstallment.max" step="500000"
            x-model.number="filters.affordableInstallment.max" @input="if(filters.affordableInstallment.max < filters.affordableInstallment.min) filters.affordableInstallment.max = filters.affordableInstallment.min">
        </div>
      </div>

      
      <div class="border-b border-gray-100 pb-4 mb-4 bg-teal-50/60 -mx-5 px-5 pt-4">
        <p class="font-bold text-[14px] text-ink-900 mb-1">💚 حداکثر مبلغی که می‌پردازم</p>
        <p class="text-[11.5px] text-ink-500 mb-3">فقط آگهی‌هایی با قیمت امتیاز کمتر یا مساوی این مبلغ نشان داده شود</p>
        <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
          <span>۰</span>
          <span x-text="formatToman(filters.maxBuyerPayment)"></span>
        </div>
        <div class="slider-wrap">
          <div class="slider-track"></div>
          <div class="slider-fill" :style="`right:0%; left:${100 - pct(filters.maxBuyerPayment, bounds.maxBuyerPayment)}%`"></div>
          <input type="range" class="range-thumb" :min="bounds.maxBuyerPayment.min" :max="bounds.maxBuyerPayment.max" step="1000000"
            x-model.number="filters.maxBuyerPayment">
        </div>
      </div>

      
      <div class="border-b border-gray-100 pb-4 mb-4">
        <p class="font-bold text-[14px] text-ink-900 mb-3">📍 موقعیت مکانی</p>
        <select x-model="filters.province" @change="onProvinceChange()" class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 mb-2 focus:outline-none focus:border-teal-500">
          <option value="">همه استان‌ها</option>
          <template x-for="p in provinces" :key="p.id">
            <option :value="p.id" x-text="p.name"></option>
          </template>
        </select>
        <select x-model="filters.city" :disabled="!filters.province" class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 disabled:bg-gray-50 disabled:text-ink-400 focus:outline-none focus:border-teal-500">
          <option value="">همه شهرها</option>
          <template x-for="c in cities" :key="c.id">
            <option :value="c.id" x-text="c.name"></option>
          </template>
        </select>
      </div>

      
      <details class="filter-group border-b border-gray-100 pb-4 mb-4">
        <summary class="cursor-pointer list-none flex items-center justify-between font-bold text-[14px] text-ink-900">
          <span>⭐ وضعیت آگهی</span>
          <svg class="w-4 h-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-3 space-y-2">
          <template x-for="opt in adStatusOptions" :key="opt.key">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="opt.key" x-model="filters.adStatus" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="opt.label"></span>
            </label>
          </template>
        </div>
      </details>

      
      <details class="filter-group border-b border-gray-100 pb-4 mb-4">
        <summary class="cursor-pointer list-none flex items-center justify-between font-bold text-[14px] text-ink-900">
          <span>🛡 نوع معامله</span>
          <svg class="w-4 h-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-3 space-y-2">
          <template x-for="opt in transactionTypeOptions" :key="opt.key">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="opt.key" x-model="filters.transactionType" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="opt.label"></span>
            </label>
          </template>
        </div>
      </details>

      
      <details class="filter-group border-b border-gray-100 pb-4 mb-4">
        <summary class="cursor-pointer list-none flex items-center justify-between font-bold text-[14px] text-ink-900">
          <span>👤 فروشنده</span>
          <svg class="w-4 h-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-3 space-y-2">
          <template x-for="opt in sellerTypeOptions" :key="opt.key">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="opt.key" x-model="filters.sellerType" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="opt.label"></span>
            </label>
          </template>
        </div>
        <div class="mt-4">
          <p class="text-[12.5px] font-semibold text-ink-600 mb-2">⭐ حداقل امتیاز فروشنده</p>
          <div class="flex justify-between text-[12px] text-ink-500 mb-1.5">
            <span x-text="filters.sellerRatingMin + ' ستاره به بالا'"></span>
          </div>
          <div class="slider-wrap">
            <div class="slider-track"></div>
            <div class="slider-fill" :style="`right:0%; left:${100 - pct(filters.sellerRatingMin, bounds.sellerRating)}%`"></div>
            <input type="range" class="range-thumb" :min="bounds.sellerRating.min" :max="bounds.sellerRating.max" step="1" x-model.number="filters.sellerRatingMin">
          </div>
        </div>
      </details>

      
      <details class="filter-group border-b border-gray-100 pb-4 mb-4">
        <summary class="cursor-pointer list-none flex items-center justify-between font-bold text-[14px] text-ink-900">
          <span>🔥 محبوبیت و زمان ثبت</span>
          <svg class="w-4 h-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-3 space-y-2">
          <template x-for="opt in popularityOptions" :key="opt.key">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="opt.key" x-model="filters.popularity" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="opt.label"></span>
            </label>
          </template>
        </div>
        <div class="mt-4">
          <p class="text-[12.5px] font-semibold text-ink-600 mb-2">📆 زمان ثبت آگهی</p>
          <select x-model="filters.registeredTime" class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
            <option value="">همه زمان‌ها</option>
            <option value="today">امروز</option>
            <option value="yesterday">دیروز</option>
            <option value="this_week">این هفته</option>
            <option value="this_month">این ماه</option>
          </select>
        </div>
      </details>

      
      <details class="filter-group pb-2 mb-4">
        <summary class="cursor-pointer list-none flex items-center justify-between font-bold text-[14px] text-ink-900">
          <span>📑 قرارداد، مذاکره و مدارک</span>
          <svg class="w-4 h-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-3 space-y-2">
          <template x-for="opt in contractOptions" :key="opt.key">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="opt.key" x-model="filters.contractStatus" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="opt.label"></span>
            </label>
          </template>
          <template x-for="opt in negotiationOptions" :key="opt.key">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="opt.key" x-model="filters.negotiationStatus" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="opt.label"></span>
            </label>
          </template>
          <template x-for="opt in documentOptions" :key="opt.key">
            <label class="flex items-center gap-2 text-[13px] text-ink-700 cursor-pointer">
              <input type="checkbox" :value="opt.key" x-model="filters.documents" class="accent-teal-600 w-4 h-4 rounded">
              <span x-text="opt.label"></span>
            </label>
          </template>
        </div>
      </details>

      
      <div class="sticky bottom-0 bg-white pt-3 -mx-5 px-5 border-t border-gray-100 flex gap-2">
        <button type="button" @click="resetFilters()" class="flex-1 border border-gray-200 rounded-xl py-3 text-[13.5px] font-semibold text-ink-700 hover:border-gray-300">
          حذف فیلترها
        </button>
        <button type="button" @click="applyFilters()" class="flex-[1.4] bg-teal-600 text-white rounded-xl py-3 text-[13.5px] font-bold hover:bg-teal-700 transition-colors shadow-lg shadow-teal-600/25">
          اعمال فیلتر
        </button>
      </div>
    </aside>

    
    <div x-cloak x-show="mobileFilters" @click="mobileFilters = false" class="lg:hidden fixed inset-0 bg-black/40 z-50"></div>

    
    <div>
      
      <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <p class="text-[13.5px] text-ink-600">
          نمایش <span class="font-bold text-ink-900" x-text="listings.length"></span> از
          <span class="font-bold text-ink-900" x-text="pagination.total.toLocaleString('en-US')"></span> آگهی
        </p>
        <select x-model="filters.sort" @change="applyFilters()" class="text-[13px] border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
          <option value="newest">جدیدترین</option>
          <option value="oldest">قدیمی‌ترین</option>
          <option value="cheapest">ارزان‌ترین</option>
          <option value="expensive">گران‌ترین</option>
          <option value="highest_loan">بیشترین مبلغ وام</option>
          <option value="lowest_profit">کمترین نرخ سود</option>
          <option value="top_seller_rating">بیشترین امتیاز فروشنده</option>
          <option value="most_viewed">بیشترین بازدید</option>
          <option value="most_popular">بیشترین محبوبیت</option>
          <option value="nearest_city">نزدیک‌ترین شهر</option>
          <option value="featured">پیشنهاد ویژه</option>
        </select>
      </div>

      
      <div x-cloak x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <template x-for="i in 6" :key="i">
          <div class="border border-gray-100 rounded-2xl p-5 h-[230px] skeleton"></div>
        </template>
      </div>

      
      <div x-cloak x-show="!loading && listings.length === 0" class="text-center py-20 border border-dashed border-gray-200 rounded-2xl">
        <p class="text-[15px] font-bold text-ink-800">آگهی‌ای با این فیلترها پیدا نشد</p>
        <p class="text-[13px] text-ink-400 mt-1">فیلترهای خود را تغییر دهید یا آن‌ها را پاک کنید</p>
        <button @click="resetFilters()" class="mt-4 text-teal-600 font-semibold text-[13.5px] hover:underline">حذف همه فیلترها</button>
      </div>

      
      <div x-cloak x-show="!loading && listings.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 stagger">
        <template x-for="loan in listings" :key="loan.id">
          <div class="card-hover relative overflow-hidden border border-gray-100 rounded-[22px] p-4 bg-white shadow-[0_14px_40px_-30px_rgba(15,23,42,0.14)] hover:-translate-y-0.5 transition-transform duration-200 font-quicksand">
            
            <div class="absolute" style="top:6px; right:14px; z-index:10; transform:translateY(-2px);">
              <div class="w-14 h-9 rounded-[10px] overflow-hidden flex items-center justify-center text-white font-bold text-[13px] shadow" :class="loan.avatarBg">
                <template x-if="loan.sellerAvatar">
                  <img :src="loan.sellerAvatar" alt="avatar" class="w-full h-full object-cover" />
                </template>
                <template x-if="!loan.sellerAvatar">
                  <span class="text-[12px] px-1 truncate" x-text="loan.bankName"></span>
                </template>
              </div>
            </div>

            
            <div class="absolute top-2 left-3 z-20">
              <div class="flex items-center gap-2">
                <span x-show="loan.urgent" class="text-[10px] font-bold bg-red-50 text-red-600 px-2 py-0.5 rounded-full shadow-sm">اورژانسی</span>
                <span x-show="loan.vip" class="text-[10px] font-bold bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full shadow-sm">VIP</span>
              </div>
            </div>

            <div class="flex items-center justify-between gap-3">
              <div></div>

              <div class="flex items-center gap-3">
                
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white font-bold text-[11px]" :class="loan.color" :title="loan.bankName">
                  <span class="text-[10px] px-1 truncate" x-text="loan.bankName"></span>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <h2 class="text-[14px] font-semibold text-ink-900 leading-tight truncate" x-text="loan.title"></h2>
              <p class="text-[10px] text-ink-500 mt-1 truncate" x-text="loan.plan"></p>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full" x-text="loan.repaymentMonths + ' ماهه'"></span>
              <span class="text-[10px] font-semibold text-cyan-700 bg-cyan-100 px-2.5 py-1 rounded-full" x-text="'سود ' + loan.profitRate + '٪'"></span>
              <span class="text-[10px] font-semibold text-violet-700 bg-violet-100 px-2.5 py-1 rounded-full" x-text="'امتیاز ' + loan.sellerRating + '★'"></span>
            </div>

            <div class="mt-5 border-t border-gray-100 pt-4">
              <p class="text-[17px] font-extrabold text-ink-900" x-text="formatToman(loan.scorePrice) + ' تومان'"></p>
              <p class="text-[12px] text-ink-500 mt-1" x-text="'مبلغ وام ' + formatToman(loan.loanAmount) + ' تومان'"></p>
              <p class="text-[12px] text-ink-500 mt-1 flex items-center gap-2">
                <span x-text="loan.city"></span>
                <span class="text-ink-400">،</span>
                <span x-text="loan.province" class="ml-1"></span>
                <span class="text-ink-400 mx-1">·</span>
                <span class="text-[12px] text-ink-500" x-text="loan.publishedJalali"></span>
              </p>
            </div>

            <button @click="goToListing(loan.id)" class="mt-5 w-full text-[13px] font-semibold py-3 rounded-2xl border border-gray-200 text-ink-900 hover:bg-slate-50 transition">
              مشاهده جزئیات
            </button>
          </div>
        </template>
      </div>

      
      <div x-cloak x-show="!loading && pagination.lastPage > 1" class="flex items-center justify-center gap-2 mt-10 flex-wrap">
        <button type="button" @click="goToPage(pagination.currentPage - 1)" :disabled="pagination.currentPage === 1"
          class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center disabled:opacity-30 hover:border-teal-500">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <template x-for="p in paginationRange" :key="p">
          <button type="button" @click="typeof p === 'number' && goToPage(p)"
            class="w-9 h-9 rounded-lg text-[13px] font-semibold flex items-center justify-center"
            :class="p === pagination.currentPage ? 'bg-teal-600 text-white' : (p === '...' ? 'text-ink-400 cursor-default' : 'border border-gray-200 hover:border-teal-500')"
            x-text="p"></button>
        </template>

        <button type="button" @click="goToPage(pagination.currentPage + 1)" :disabled="pagination.currentPage === pagination.lastPage"
          class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center disabled:opacity-30 hover:border-teal-500">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>
  </div>
</section>

<?= view('components.landing.footer')->render(); ?>

<script>
window.listingsApp = function listingsApp() {
  return {
    mobileMenu: false,
    mobileFilters: false,
    loading: true,

    banksList: @json($banks ?? []),
    bankPlans: @json($bankPlans ?? []),
    bankDataLoaded: false,
    isAuthenticated: false,
    user: null,
    provinces: [], // loaded from API: [{id, name, name_en, cities_count}, ...]
    cities: [],    // loaded for selected province: [{id, name, name_en, is_capital}, ...]
    adStatusOptions: [
      { key: 'active', label: 'فقط فعال' }, { key: 'unsold', label: 'فروخته نشده' },
      { key: 'negotiating', label: 'دارای مذاکره' }, { key: 'urgent', label: 'فوری' },
      { key: 'vip', label: 'VIP' }, { key: 'guaranteed', label: 'دارای ضمانت سایت' },
    ],
    transactionTypeOptions: [
      { key: 'escrow', label: 'Escrow' }, { key: 'vip_no_escrow', label: 'VIP بدون Escrow' },
      { key: 'in_person', label: 'فقط حضوری' }, { key: 'online', label: 'فقط آنلاین' },
    ],
    sellerTypeOptions: [
      { key: 'verified', label: 'احراز هویت شده' }, { key: 'vip', label: 'VIP' },
      { key: 'featured', label: 'فروشنده منتخب' }, { key: 'high_rating', label: 'دارای امتیاز بالا' },
    ],
    popularityOptions: [
      { key: 'most_viewed', label: 'پربازدید' }, { key: 'most_contacted', label: 'بیشترین تماس' },
      { key: 'most_negotiated', label: 'بیشترین مذاکره' }, { key: 'most_sold', label: 'بیشترین فروش' },
    ],
    contractOptions: [
      { key: 'contract_ready', label: 'قرارداد آماده' }, { key: 'needs_contract', label: 'نیازمند قرارداد' },
    ],
    negotiationOptions: [
      { key: 'negotiable', label: 'قابل مذاکره' }, { key: 'fixed_price', label: 'قیمت قطعی' },
    ],
    documentOptions: [
      { key: 'full_verification', label: 'احراز هویت کامل' }, { key: 'esign', label: 'امضای الکترونیکی ثبت شده' },
      { key: 'full_docs', label: 'مدارک کامل' },
    ],
    quickChips: [
      { key: 'urgent', label: '🔥 فوری' }, { key: 'vip', label: '⭐ VIP' },
      { key: 'guaranteed', label: '🛡 ضمانت سایت' }, { key: 'verified', label: '✅ احراز هویت شده' },
      { key: 'under_20m', label: '💰 زیر ۲۰ میلیون' }, { key: 'bank_melli', label: '🏦 بانک ملی' },
      { key: 'bank_resalat', label: '🏦 بانک رسالت' }, { key: 'profit_under_4', label: '📈 سود زیر ۴٪' },
      { key: 'months_60', label: '📅 اقساط ۶۰ ماهه' }, { key: 'negotiable', label: '💬 قابل مذاکره' },
      { key: 'newest', label: '🆕 جدیدترین' },
    ],

    bounds: {
      loanAmount:          { min: 50000000,  max: 1000000000 },
      scorePrice:          { min: 5000000,   max: 300000000 },
      profitRate:          { min: 0,         max: 30 },
      repaymentMonths:     { min: 10,        max: 60 },
      installmentAmount:   { min: 500000,    max: 50000000 },
      affordableInstallment:{ min: 500000,   max: 30000000 },
      maxBuyerPayment:     { min: 0,         max: 300000000 },
      sellerRating:        { min: 1,         max: 5 },
    },

    filters: {
      banks: [],
      plan: '',
      loanAmount: { min: 50000000, max: 1000000000 },
      scorePrice: { min: 5000000, max: 300000000 },
      profitRate: { min: 0, max: 30 },
      repaymentMonths: { min: 10, max: 60 },
      installmentAmount: { min: 500000, max: 50000000 },
      affordableInstallment: { min: 500000, max: 30000000 },
      maxBuyerPayment: 300000000,
      province: '',
      city: '',
      adStatus: [],
      transactionType: [],
      sellerType: [],
      sellerRatingMin: 1,
      popularity: [],
      registeredTime: '',
      contractStatus: [],
      negotiationStatus: [],
      documents: [],
      sort: 'newest',
    },

    pagination: { currentPage: 1, perPage: 9, total: 0, lastPage: 1 },
    listings: [],
    // loading states
    filtersLoading: false,
    provincesLoading: false,
    citiesLoading: false,

    async init() {
      // prepare debounced fetch to avoid many requests on slider changes
      this._debouncedFetch = this._debounce(() => { this.fetchListings(); }, 350);

      this._applyLocalTokenIfAny();

      if (! Array.isArray(this.banksList) || this.banksList.length === 0) {
        await this.loadBanks();
      } else {
        this.bankDataLoaded = true;
        this._normalizeBankData();
      }

      await this.checkAuthentication();

      await this.loadProvinces();
      if (this.filters.province) {
        await this.loadCities(this.filters.province);
      }
      this.fetchListings();
    },

    async loadProvinces() {
      this.provincesLoading = true;
      try {
        const { data } = await axios.get('/api/v1/locations/provinces');
        this.provinces = Array.isArray(data.data) ? data.data : data;
      } catch (err) {
        console.error('Failed to load provinces', err);
        this.provinces = [];
      } finally {
        this.provincesLoading = false;
      }
    },

    async loadCities(provinceId) {
      if (! provinceId) { this.cities = []; return; }
      this.citiesLoading = true;
      try {
        const { data } = await axios.get(`/api/v1/locations/provinces/${provinceId}/cities`);
        this.cities = Array.isArray(data.data) ? data.data : data;
      } catch (err) {
        console.error('Failed to load cities for province', provinceId, err);
        this.cities = [];
      } finally {
        this.citiesLoading = false;
      }
    },

    onProvinceChange() {

      this.filters.city = '';
      if (this.filters.province) {
        this.loadCities(this.filters.province);
      } else {
        this.cities = [];
      }
      this.applyFilters();
    },

    async loadBanks() {
      try {
        const { data } = await axios.get('/api/v1/banks');
        this.banksList = Array.isArray(data.banks) ? data.banks : this.banksList;
        this.bankPlans = data.bank_plans || this.bankPlans;
        this._normalizeBankData();
      } catch (err) {
      } finally {
        this.bankDataLoaded = true;
      }
    },

    _applyLocalTokenIfAny() {
      const keys = ['sanctum_token', 'auth_token', 'token', 'access_token', 'user_authenticated'];
      for (const k of keys) {
        const v = localStorage.getItem(k);
        if (v) {
          if (k === 'user_authenticated' && (v === '1' || v === 'true')) {
            this.isAuthenticated = true;
            continue;
          }
          axios.defaults.headers.common['Authorization'] = `Bearer ${v}`;
          this.isAuthenticated = true;
          break;
        }
      }
    },

    async checkAuthentication() {
      try {
        const { data } = await axios.get('/api/v1/auth/me', { headers: { Accept: 'application/json' } });
        this.isAuthenticated = !!(data?.user?.id) || this.isAuthenticated;
        this.user = data?.user ?? this.user;
        if (this.isAuthenticated) localStorage.setItem('user_authenticated', '1');
      } catch (err) {
        if (!this.isAuthenticated) {
          this.isAuthenticated = false;
          this.user = null;
          localStorage.removeItem('user_authenticated');
        }
      }
    },

    // debounce helper
    _debounce(fn, wait = 300) {
      let t = null;
      return (...args) => {
        if (t) clearTimeout(t);
        t = setTimeout(() => { fn.apply(this, args); t = null; }, wait);
      };
    },

    // Ensure banksList is array of objects and bankPlans is a mapping { bank_key: [plans] }
    _normalizeBankData() {
      if (Array.isArray(this.banksList)) {
        this.banksList = this.banksList.map((b) => {
          if (typeof b === 'string') return { key: b, name: b };
          if (!b.key && b.code) b.key = String(b.code);
          if (!b.key && b.id) b.key = String(b.id);
          // common normalization: lowercase key
          if (b.key) b.key = String(b.key).toLowerCase();
          return b;
        });
      } else {
        this.banksList = [];
      }

      if (Array.isArray(this.bankPlans)) {
        const map = {};
        this.bankPlans.forEach((p) => {
          const key = (p.bank_key || p.bank || (p.bank_id ? String(p.bank_id) : null));
          if (!key) return;
          const k = String(key).toLowerCase();
          map[k] = map[k] || [];
          const plan = p.plan || p.name || p.code || p.id;
          if (plan && !map[k].includes(plan)) map[k].push(plan);
        });
        this.bankPlans = map;
      } else if (this.bankPlans && typeof this.bankPlans === 'object') {
        // normalize keys to lowercase
        const normalized = {};
        Object.keys(this.bankPlans).forEach((k) => { normalized[String(k).toLowerCase()] = this.bankPlans[k]; });
        this.bankPlans = normalized;
      } else {
        this.bankPlans = {};
      }
    },

    // map quick-chip keys to actual bank keys (tolerant)
    _bankKeyForChip(chipKey) {
      const map = {
        bank_melli: 'melli',
        bank_resalat: 'resalat',
      };
      return map[chipKey] || chipKey;
    },

    // Ensure banksList is array of objects and bankPlans is a mapping { bank_key: [plans] }
    _normalizeBankData() {
      // normalize banksList: ensure each bank has a `key` string identifier
      if (Array.isArray(this.banksList)) {
        this.banksList = this.banksList.map((b) => {
          if (typeof b === 'string') return { key: b, name: b };
          if (!b.key && b.code) b.key = String(b.code);
          if (!b.key && b.id) b.key = String(b.id);
          return b;
        });
      } else {
        this.banksList = [];
      }

      // normalize bankPlans: if it's an array, convert to mapping by bank key
      if (Array.isArray(this.bankPlans)) {
        const map = {};
        this.bankPlans.forEach((p) => {
          // expect p to have bank_key or bank_id and plan identifier
          const key = p.bank_key || p.bank || (p.bank_id ? String(p.bank_id) : null);
          if (!key) return;
          map[key] = map[key] || [];
          const plan = p.plan || p.name || p.code || p.id;
          if (plan && !map[key].includes(plan)) map[key].push(plan);
        });
        this.bankPlans = map;
      } else if (this.bankPlans && typeof this.bankPlans === 'object') {
        // already mapping — leave as is
      } else {
        this.bankPlans = {};
      }
    },

    goToLogin() { if (this.isAuthenticated) return this.goToDashboard(); window.location.href = '/users/login'; },
    goToRegister() { if (this.isAuthenticated) return this.goToDashboard(); window.location.href = '/users/register'; },
    goToListing(id) { window.location.href = `/ads/detail?id=${id}`; },
    goToLoans() { window.location.href = '/ads/loadLoans'; },
    goToGuide() { document.getElementById('how-it-works')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); },
    goToAbout() { document.getElementById('why-us')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); },
    goToContact() { document.getElementById('cta')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); },
    goToBlog() { window.location.href = '/blog'; },
    goToDashboard() { window.location.href = '/dashboard'; },

    formatToman(n) { return Number(n).toLocaleString('en-US'); },

    pct(value, bound) {
      if (bound.max === bound.min) return 0;
      return ((value - bound.min) / (bound.max - bound.min)) * 100;
    },

    get availablePlans() {
      const banks = Array.isArray(this.filters?.banks) ? this.filters.banks : [];
      if (banks.length === 0) return [];

      const plans = new Set();
      const bankPlans = this.bankPlans && typeof this.bankPlans === 'object' ? this.bankPlans : {};

      banks.forEach((bank) => {
        const key = String(bank ?? '').trim().toLowerCase();
        const directList = Array.isArray(bankPlans[bank]) ? bankPlans[bank] : [];
        const normalizedList = Array.isArray(bankPlans[key]) ? bankPlans[key] : directList;

        normalizedList.forEach((plan) => {
          if (plan !== null && plan !== undefined && plan !== '') {
            plans.add(String(plan));
          }
        });
      });

      return Array.from(plans);
    },

    get hasAvailablePlans() {
      return Array.isArray(this.availablePlans) && this.availablePlans.length > 0;
    },

    onBankFilterChange() {
      if (this.filters.plan && !this.availablePlans.includes(this.filters.plan)) {
        this.filters.plan = '';
      }
    },

    get paginationRange() {
      const total = this.pagination.lastPage;
      const current = this.pagination.currentPage;
      const range = [];
      const delta = 1;
      for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
          range.push(i);
        } else if (range[range.length - 1] !== '...') {
          range.push('...');
        }
      }
      return range;
    },

    isQuickChipActive(chip) {
      switch (chip.key) {
        case 'urgent': return this.filters.adStatus.includes('urgent');
        case 'vip': return this.filters.adStatus.includes('vip');
        case 'guaranteed': return this.filters.adStatus.includes('guaranteed');
        case 'verified': return this.filters.sellerType.includes('verified');
        case 'under_20m': return this.filters.scorePrice.max <= 20000000;
        case 'bank_melli': return this.filters.banks.includes(this._bankKeyForChip('bank_melli'));
        case 'bank_resalat': return this.filters.banks.includes(this._bankKeyForChip('bank_resalat'));
        case 'profit_under_4': return this.filters.profitRate.max <= 4;
        case 'months_60': return this.filters.repaymentMonths.min === 60 && this.filters.repaymentMonths.max === 60;
        case 'negotiable': return this.filters.negotiationStatus.includes('negotiable');
        case 'newest': return this.filters.sort === 'newest';
        default: return false;
      }
    },

    toggleQuickChip(chip) {
      const active = this.isQuickChipActive(chip);
      switch (chip.key) {
        case 'urgent': this.toggleInArray(this.filters.adStatus, 'urgent'); break;
        case 'vip': this.toggleInArray(this.filters.adStatus, 'vip'); break;
        case 'guaranteed': this.toggleInArray(this.filters.adStatus, 'guaranteed'); break;
        case 'verified': this.toggleInArray(this.filters.sellerType, 'verified'); break;
        case 'under_20m': this.filters.scorePrice.max = active ? this.bounds.scorePrice.max : 20000000; break;
        case 'bank_melli': this.toggleInArray(this.filters.banks, this._bankKeyForChip('bank_melli')); break;
        case 'bank_resalat': this.toggleInArray(this.filters.banks, this._bankKeyForChip('bank_resalat')); break;
        case 'profit_under_4': this.filters.profitRate.max = active ? this.bounds.profitRate.max : 4; break;
        case 'months_60':
          if (active) { this.filters.repaymentMonths.min = this.bounds.repaymentMonths.min; this.filters.repaymentMonths.max = this.bounds.repaymentMonths.max; }
          else { this.filters.repaymentMonths.min = 60; this.filters.repaymentMonths.max = 60; }
          break;
        case 'negotiable': this.toggleInArray(this.filters.negotiationStatus, 'negotiable'); break;
        case 'newest': this.filters.sort = 'newest'; break;
      }
      this.applyFilters();
    },

    toggleInArray(arr, val) {
      const i = arr.indexOf(val);
      if (i === -1) arr.push(val); else arr.splice(i, 1);
    },

    resetFilters() {
      this.filters = {
        banks: [], plan: '',
        loanAmount: { ...this.bounds.loanAmount },
        scorePrice: { ...this.bounds.scorePrice },
        profitRate: { ...this.bounds.profitRate },
        repaymentMonths: { ...this.bounds.repaymentMonths },
        installmentAmount: { ...this.bounds.installmentAmount },
        affordableInstallment: { ...this.bounds.affordableInstallment },
        maxBuyerPayment: this.bounds.maxBuyerPayment?.max ?? 300000000,
        province: '', city: '',
        adStatus: [], transactionType: [], sellerType: [],
        sellerRatingMin: 1, popularity: [], registeredTime: '',
        contractStatus: [], negotiationStatus: [], documents: [],
        sort: 'newest',
      };
      this.applyFilters();
    },

    applyFilters() {
      this.pagination.currentPage = 1;
      this.mobileFilters = false;
      // use debounced fetch to reduce server load when multiple filters change rapidly
      if (this._debouncedFetch) this._debouncedFetch(); else this.fetchListings();
    },

    goToPage(page) {
      if (page < 1 || page > this.pagination.lastPage) return;
      this.pagination.currentPage = page;
      this.fetchListings();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    getListingsApiUrl() {
      return '/api/advertisements';
    },

    normalizeListingsResponse(payload) {
      const items = Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : [];
      const meta = payload?.meta ?? {};

      return { items, meta };
    },

    async fetchListings() {
      this.loading = true;

      try {
        const params = this.buildParams();
        const { data } = await axios.get(this.getListingsApiUrl(), { params });
        const normalized = this.normalizeListingsResponse(data);

        this.listings = normalized.items.map((item) => this.mapListing(item));
        this.pagination.total = Number(normalized.meta.total ?? normalized.items.length);
        this.pagination.lastPage = Math.max(1, Math.ceil(this.pagination.total / this.pagination.perPage));
        this.pagination.currentPage = Number(normalized.meta.current_page ?? this.pagination.currentPage);
      } catch (err) {
        this.listings = [];
        this.pagination.total = 0;
        this.pagination.lastPage = 1;
      } finally {
        this.loading = false;
      }
    },

    buildParams() {
      const f = this.filters;
      return {
        page: this.pagination.currentPage,
        per_page: this.pagination.perPage,
        sort: f.sort,
        banks: f.banks,
        plan: f.plan || undefined,
        loan_amount_min: f.loanAmount.min,
        loan_amount_max: f.loanAmount.max,
        score_price_min: f.scorePrice.min,
        score_price_max: f.scorePrice.max,
        profit_min: f.profitRate.min,
        profit_max: f.profitRate.max,
        repayment_months_min: f.repaymentMonths.min,
        repayment_months_max: f.repaymentMonths.max,
        installment_min: f.installmentAmount.min,
        installment_max: f.installmentAmount.max,
        affordable_installment_min: f.affordableInstallment.min,
        affordable_installment_max: f.affordableInstallment.max,
        max_buyer_payment: f.maxBuyerPayment,
        province_id: f.province || undefined,
        city_id: f.city || undefined,
        ad_status: f.adStatus,
        transaction_type: f.transactionType,
        seller_type: f.sellerType,
        seller_rating_min: f.sellerRatingMin,
        popularity: f.popularity,
        registered_time: f.registeredTime || undefined,
        contract_status: f.contractStatus,
        negotiation_status: f.negotiationStatus,
        documents: f.documents,
      };
    },

    mapListing(item) {
      const bankName = item.bank_name || item.bank?.name || '';
      const sellerName = item.seller?.name || item.seller?.full_name || item.seller?.display_name || item.seller_name || '';
      const avatarTextSource = bankName || item.title || sellerName || 'آگهی';

      let initials = '';
      if (bankName) {
        initials = bankName.trim().split(/\s+/)[0];
      } else {
        initials = avatarTextSource.trim().slice(0, 2);
      }

      const palette = ['bg-emerald-500','bg-amber-500','bg-pink-500','bg-red-500','bg-purple-500','bg-sky-500','bg-teal-500','bg-rose-500','bg-indigo-500'];
      const colorIndex = Math.floor(Math.random() * palette.length);
      const publishedJalali = item.published_at_jalali || item.published_at_label || '';

      return {
        id: item.id,
        uuid: item.uuid,
        title: bankName ? `${item.title} - ${bankName}` : item.title,
        bankName: bankName || sellerName || item.title || '',
        plan: item.plan || item.loan_plan || 'طرح عمومی',
        bankInitial: (bankName || sellerName || item.title || '?').trim()[0],
        loanAmount: item.loan_amount ?? item.loanAmount ?? item.price,
        scorePrice: item.price ?? item.score_price ?? 0,
        profitRate: item.interest_rate ?? item.profit_rate ?? 0,
        repaymentMonths: item.installment_count ?? item.repayment_months ?? 12,
        sellerRating: item.seller?.rating ?? item.seller_rating ?? 0,
        province: item.province || item.province_id || 'تهران',
        city: item.city || item.city_id || 'تهران',
        publishedJalali,

        urgent: Boolean(item.urgent),
        vip: Boolean(item.vip),
        priority: item.priority ?? 0,
        priorityLabel: item.priority_label || '',
        seller: item.seller,
        sellerAvatar: item.seller?.avatar_url || item.seller?.avatar || '',
        sellerInitials: initials,
        avatarBg: item.vip ? 'bg-emerald-500' : (item.urgent ? 'bg-amber-500' : palette[colorIndex]),
      };
    },

  };
}
</script>

</body>
</html>
