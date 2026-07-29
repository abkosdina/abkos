<section class="max-w-[1180px] mx-auto px-5 pt-14 pb-10 grid lg:grid-cols-2 gap-12 items-center">
    <div class="order-2 lg:order-1">
        <h1 class="text-[34px] sm:text-[42px] leading-[1.35] font-extrabold text-ink-900 animate-fadeUp">
            امتیاز وام بانک‌ها را<br> معامله کنید
        </h1>
        <p class="mt-5 text-ink-600 text-[15.5px] leading-8 max-w-[480px] animate-fadeUp" style="animation-delay:.15s">
            سرمایه‌گذاران امتیاز وام مازاد خود را به فروش می‌رسانند و متقاضیان با امتیاز بالاتر، سریع‌تر وام می‌گیرند.
        </p>

        <div class="mt-7 flex flex-wrap gap-3 animate-fadeUp" style="animation-delay:.3s">
            <button @click="openAdModal()" class="px-6 py-3.5 rounded-xl border-2 border-teal-600 text-teal-700 font-semibold text-[14.5px] hover:bg-teal-50 transition-colors">
                ثبت آگهی فروش
            </button>
            <a  href='' @click.prevent="goToLoans()"  class="btn-shine px-6 py-3.5 rounded-xl bg-teal-600 text-white font-semibold text-[14.5px] hover:bg-teal-700 transition-colors shadow-lg shadow-teal-600/20">
                مشاهده آگهی ها
            </a>
        </div>

        <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-[13px] text-ink-600 animate-fadeUp" style="animation-delay:.45s">
            <span class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> کاملاً قانونی و شفاف</span>
            <span class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> بدون دخالت در فرآیند بانک</span>
            <span class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> انتقال امن و سریع</span>
        </div>
    </div>

    <div class="order-1 lg:order-2 relative h-[430px]">
        <div class="absolute right-0 top-4 w-[88%] max-w-[430px] bg-white rounded-2xl shadow-2xl shadow-teal-900/10 border border-gray-100 p-5 animate-floatySlow">
            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                <div class="w-9 h-9 rounded-full bg-teal-100 flex items-center justify-center font-bold text-teal-700 text-sm">م</div>
                <div>
                    <p class="text-[13px] font-bold text-ink-900">محمد رضایی</p>
                    <p class="text-[11px] text-ink-400">سرمایه گذار</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4">
                <div class="bg-teal-50 rounded-xl p-2.5 text-center">
                    <p class="text-[15px] font-extrabold text-teal-700">12</p>
                    <p class="text-[10px] text-ink-400 mt-0.5">آگهی فعال</p>
                </div>
                <div class="bg-teal-50 rounded-xl p-2.5 text-center">
                    <p class="text-[15px] font-extrabold text-teal-700">254,000</p>
                    <p class="text-[10px] text-ink-400 mt-0.5">امتیاز فروخته شده</p>
                </div>
                <div class="bg-teal-50 rounded-xl p-2.5 text-center">
                    <p class="text-[13px] font-extrabold text-teal-700">1,250,000,000</p>
                    <p class="text-[10px] text-ink-400 mt-0.5">درآمد کل</p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-[12px] font-semibold text-ink-800 mb-2">نمودار فروش امتیاز</p>
                <svg viewBox="0 0 380 110" class="w-full h-[100px]">
                    <polyline class="dash-line" points="0,80 40,70 80,85 120,55 160,65 200,30 240,45 280,20 320,35 360,10"
                        fill="none" stroke="#0f9c8c" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="360" cy="10" r="5" fill="#0f9c8c" class="animate-pulseSoft" style="transform-origin:360px 10px"/>
                </svg>
            </div>
        </div>

        <div class="absolute left-0 bottom-0 w-[200px] sm:w-[230px] animate-floaty">
            <div class="bg-ink-900 rounded-[2.2rem] p-2.5 shadow-2xl">
                <div class="bg-white rounded-[1.7rem] overflow-hidden">
                    <div class="bg-teal-600 h-10 flex items-center justify-between px-3">
                        <span class="text-white text-[10px] font-bold">مستر وام</span>
                        <div class="w-4 h-4 rounded bg-white/30"></div>
                    </div>
                    <div class="p-2.5 space-y-2">
                        <div class="bg-gray-50 rounded-lg h-6 flex items-center px-2 text-[9px] text-ink-400">جستجوی امتیاز...</div>
                        <template x-for="item in phoneListings" :key="item.id">
                            <div class="border border-gray-100 rounded-lg p-2 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full shrink-0" :class="item.color"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[9px] font-bold truncate" x-text="item.title"></p>
                                    <p class="text-[8px] text-teal-600 font-semibold" x-text="'امتیاز ' + item.score"></p>
                                </div>
                                <p class="text-[8px] font-bold text-ink-800" x-text="formatToman(item.price)"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ad creation modal -->
<div x-show="isAdModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div @click.away="isAdModalOpen = false" class="bg-white rounded-xl w-full max-w-2xl p-6">
        <h3 class="text-lg font-bold mb-3">ثبت آگهی جدید</h3>
        <div class="space-y-3">
            <input x-model="adForm.title" placeholder="عنوان آگهی" class="w-full border rounded p-2" />
            <textarea x-model="adForm.description" placeholder="توضیحات" class="w-full border rounded p-2 h-28"></textarea>
            <div class="grid grid-cols-2 gap-2">
                <select x-model="adForm.province_id" @change="fetchCitiesForProvince(adForm.province_id)" class="border rounded p-2 text-black bg-white placeholder-black">
                    <option value="" class="text-black">انتخاب استان (اختیاری)</option>
                    <template x-for="p in provinces" :key="p.id">
                        <option class="text-black" :value="p.id" x-text="p.name"></option>
                    </template>
                </select>
                <select x-model="adForm.city_id" class="border rounded p-2 text-black bg-white placeholder-black">
                    <option value="" class="text-black">انتخاب شهر (اختیاری)</option>
                    <template x-for="c in cities" :key="c.id">
                        <option class="text-black" :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9,]*"
                    :value="formatMoneyInput(adForm.loan_offer.loan_amount)"
                    @input="adForm.loan_offer.loan_amount = formatMoneyInput($event.target.value)"
                    placeholder="مبلغ وام"
                    class="border rounded p-2"
                />
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9,]*"
                    :value="formatMoneyInput(adForm.loan_offer.sale_price)"
                    @input="adForm.loan_offer.sale_price = formatMoneyInput($event.target.value)"
                    placeholder="قیمت"
                    class="border rounded p-2"
                />
            </div>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <select x-model="adForm.loan_offer.bank_id" @change="onBankChange(adForm.loan_offer.bank_id)" class="border rounded p-2">
                    <option value="">انتخاب بانک (الزامی)</option>
                    <template x-for="b in banksList" :key="b.id">
                        <option :value="b.id" x-text="b.name"></option>
                    </template>
                </select>

                <select x-model="adForm.loan_offer.loan_plan_id" class="border rounded p-2">
                    <option value="">انتخاب طرح (الزامی)</option>
                    <template x-for="p in plansForSelectedBank" :key="p.id">
                        <option :value="p.id" x-text="p.name"></option>
                    </template>
                </select>
            </div>

            <div class="mt-2">
                <p class="text-sm text-ink-600">قیمت هر میلیون امتیاز: <span class="font-bold" x-text="formatToman(computePricePerMillion()) + ' تومان'"></span></p>
            </div>

            <div class="mt-3 p-3 bg-gray-50 rounded">
                <p class="text-sm font-semibold">جزئیات طرح انتخاب‌شده (غیرقابل‌ویرایش)</p>
                <template x-if="adForm.loan_offer.loan_plan_id">
                    <div class="mt-2 text-sm text-ink-700">
                        <p>نرخ سود: <span x-text="(plansForSelectedBank.find(p=>p.id==adForm.loan_offer.loan_plan_id)?.interest_rate ?? '—') + '%' "></span></p>
                        <p>مدت بازپرداخت: <span x-text="plansForSelectedBank.find(p=>p.id==adForm.loan_offer.loan_plan_id)?.duration_months ?? '—' "></span> ماه</p>
                        <p>تعداد اقساط: <span x-text="plansForSelectedBank.find(p=>p.id==adForm.loan_offer.loan_plan_id)?.installment_count ?? '—' "></span></p>
                        <p class="mt-1">توضیحات: <span x-text="plansForSelectedBank.find(p=>p.id==adForm.loan_offer.loan_plan_id)?.description ?? '—' "></span></p>
                    </div>
                </template>
                <template x-if="!adForm.loan_offer.loan_plan_id">
                    <p class="mt-2 text-sm text-ink-500">طرحی انتخاب نشده است.</p>
                </template>
            </div>
            <div class="flex items-center justify-end gap-2 mt-4">
                <button @click="isAdModalOpen = false" class="px-4 py-2 rounded border">لغو</button>
                <button @click="submitAd()" class="px-4 py-2 rounded bg-teal-600 text-white">ثبت آگهی</button>
            </div>
        </div>
    </div>
</div>
