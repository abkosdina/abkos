<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>جزئیات آگهی امتیاز وام | مستر وام</title>

<link rel="stylesheet" href="{{ asset('cdn/Vazirmatn-font-face.css') }}">
@vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])

<style>
  html { scroll-behavior: smooth; }
  body { font-family: 'Vazirmatn', sans-serif; }
  [x-cloak] { display: none !important; }
  .stagger > * { animation: fadeUp .6s cubic-bezier(.22,1,.36,1) both; }
  .stagger > *:nth-child(1){animation-delay:.03s} .stagger > *:nth-child(2){animation-delay:.07s}
  .stagger > *:nth-child(3){animation-delay:.11s} .stagger > *:nth-child(4){animation-delay:.15s}
  .stagger > *:nth-child(5){animation-delay:.19s}
  .card-hover { transition: transform .3s cubic-bezier(.22,1,.36,1), box-shadow .3s ease; }
  .card-hover:hover { transform: translateY(-6px); box-shadow: 0 20px 40px -18px rgba(15,156,140,.3); }
  .skeleton { background: linear-gradient(90deg,#eef4f3 25%,#f7fbfa 37%,#eef4f3 63%); background-size:400px 100%; animation: shimmer 1.4s infinite linear; }
  .btn-shine { position: relative; overflow: hidden; }
  .btn-shine::after { content:''; position:absolute; inset:0; background:linear-gradient(120deg, transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%); transform:translateX(-120%); transition:transform .7s ease; }
  .btn-shine:hover::after { transform: translateX(120%); }
  ::-webkit-scrollbar { width: 7px; }
  ::-webkit-scrollbar-thumb { background: #cdeee8; border-radius: 8px; }
</style>
</head>

<body class="bg-white text-ink-900 antialiased" x-data="listingDetailApp()" x-init="init()">

<!-- ============ HEADER ============ -->
@include('components.ads.detail.header')

<!-- ============ BREADCRUMB ============ -->
<div class="max-w-[1180px] mx-auto px-5 pt-6 text-[13px] text-ink-400 flex items-center gap-1.5 flex-wrap">
  <a href="/" class="hover:text-teal-600">خانه</a>
  <span>/</span>
  <a href="/ads/loadLoans" class="hover:text-teal-600">آگهی ها</a>
  <span>/</span>
  <span class="text-ink-700" x-text="loading ? 'در حال بارگذاری...' : listing?.title"></span>
</div>

<!-- ============ LOADING SKELETON ============ -->
<section x-cloak x-show="loading" class="max-w-[1180px] mx-auto px-5 py-8 grid lg:grid-cols-[380px_1fr] gap-8">
  <div class="h-[420px] rounded-2xl skeleton"></div>
  <div class="h-[420px] rounded-2xl skeleton"></div>
</section>

<!-- ============ NOT FOUND ============ -->
<section x-cloak x-show="!loading && !listing" class="max-w-[1180px] mx-auto px-5 py-24 text-center">
  <p class="text-[18px] font-bold text-ink-800">این آگهی پیدا نشد</p>
  <p class="text-[13.5px] text-ink-400 mt-1">ممکن است حذف شده یا آدرس اشتباه باشد</p>
  <a href="/ads/loadLoans" class="inline-block mt-5 text-teal-600 font-semibold hover:underline">بازگشت به لیست آگهی‌ها</a>
</section>

<!-- ============ MAIN CONTENT ============ -->
<section x-cloak x-show="!loading && listing" class="max-w-[1180px] mx-auto px-5 py-8">
  <div class="grid lg:grid-cols-[380px_1fr] gap-8 items-start stagger">

    <!-- ============ RIGHT: PRICE / CTA / SELLER CARD (sticky) ============ -->
    <aside class="lg:sticky lg:top-24 space-y-5">
      @include('components.ads.detail.price-cta')
    </aside>

    <!-- ============ LEFT: FULL DETAILS ============ -->
    <div>
      @include('components.ads.detail.full-details')
    </div>
  </div>

  @include('components.ads.detail.similar-listings')
</section>

<!-- ============ FOOTER (same as home/listing pages) ============ -->
@include('components.landing.footer')

<script>
function listingDetailApp() {
  return {
    mobileMenu: false,
    loading: true,
    loadingSeller: true,
    listing: null,
    sellerListings: [],

    init() {
      const params = new URLSearchParams(window.location.search);
      const id = params.get('id');
      if (!id) {
        this.loading = false;
        this.loadingSeller = false;
        return;
      }
      this.fetchListingDetail(id);
    },

    goToLogin() { window.location.href = '/users/login'; },
    goToRegister() { window.location.href = '/users/register'; },
    goToListing(id) { window.location.href = `/ads/detail?id=${id}`; },
    startNegotiation() { window.location.href = '/dashboard'; },

    formatToman(n) { return n ? Number(n).toLocaleString('en-US') : ''; },

    transactionTypeLabel(type) {
      const map = { escrow: 'معامله با Escrow', vip_no_escrow: 'VIP بدون Escrow', in_person: 'فقط حضوری', online: 'فقط آنلاین' };
      return map[type] || '';
    },

    get detailRows() {
      if (!this.listing) return [];
      const l = this.listing;
      return [
        { label: 'بانک', value: l.bankName },
        { label: 'طرح وام', value: l.plan },
        { label: 'مبلغ وام', value: this.formatToman(l.loanAmount) + ' تومان' },
        { label: 'قیمت فروش امتیاز', value: this.formatToman(l.scorePrice) + ' تومان' },
        { label: 'نرخ سود', value: l.profitRate + '٪' },
        { label: 'تعداد اقساط', value: l.repaymentMonths + ' قسط' },
        { label: 'مدت بازپرداخت', value: l.repaymentMonths + ' ماه' },
        { label: 'مبلغ هر قسط', value: this.formatToman(l.installmentAmount) + ' تومان' },
        { label: 'استان', value: l.province },
        { label: 'شهر', value: l.city },
        { label: 'امتیاز فروشنده', value: l.seller.rating + ' از ۵' },
        { label: 'نوع معامله', value: this.transactionTypeLabel(l.transactionType) },
      ];
    },

    // rest of JS same as previous implementation — kept for brevity
    async fetchListingDetail(id) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/advertisements/${encodeURIComponent(id)}`);
        const item = response.data?.data ?? response.data;
        this.listing = this.mapListing(item);
      } catch (err) {
        console.error('خطا در دریافت جزئیات آگهی:', err);
        this.listing = null;
      } finally {
        this.loading = false;
        if (this.listing) {
          this.fetchSellerListings(this.listing.sellerId, this.listing.id);
        } else {
          this.loadingSeller = false;
        }
      }
    },

    async fetchSellerListings(sellerId, excludeId) {
      this.loadingSeller = true;
      try {
        const response = await axios.get('/api/advertisements', {
          params: { seller_user_id: sellerId, exclude_id: excludeId, per_page: 5 }
        });
        const items = response.data?.data ?? response.data;
        this.sellerListings = (items || []).map((it) => this.mapListing(it));
        
        // Extract total active ads count from pagination metadata
        const total = response.data?.meta?.total ?? response.data?.total ?? this.sellerListings.length;
        if (this.listing?.seller) {
          this.listing.seller.activeAdsCount = total;
        }
      } catch (err) {
        console.error('خطا در دریافت آگهی‌های فروشنده:', err);
        this.sellerListings = [];
        // Set a default value if fetch fails
        if (this.listing?.seller) {
          this.listing.seller.activeAdsCount = 0;
        }
      } finally {
        this.loadingSeller = false;
      }
    },

    mapListing(item) {
      // Enrich seller data with computed fields
      const seller = item.seller ? {
        ...item.seller,
        joinedAt: item.seller.joinedAt || item.seller.joined_at || this._formatJoinDate(item.seller.created_at),
      } : {};

      return {
        id: item.id,
        sellerId: item.seller_id ?? item.seller?.id,
        title: item.title,
        plan: item.plan,
        bankName: item.bank_name,
        bankInitial: (item.bank_name || item.title || '?').trim()[0],
        color: item.color || 'bg-teal-500',
        loanAmount: item.loan_amount ?? item.loanAmount ?? item.loan_offer?.loan_amount,
        scorePrice: item.score_price ?? item.scorePrice ?? item.price ?? item.loan_offer?.sale_price ?? 0,
        profitRate: item.profit_rate ?? item.interest_rate ?? item.loan_offer?.interest_rate ?? 0,
        repaymentMonths: item.repayment_months ?? item.installment_count ?? item.repaymentMonths ?? item.loan_offer?.installment_count ?? 0,
        installmentAmount: item.installment_amount ?? item.monthly_installment ?? item.installmentAmount ?? item.loan_offer?.monthly_installment ?? 0,
        province: item.province,
        city: item.city,
        urgent: item.urgent,
        vip: item.vip,
        guaranteed: item.guaranteed,
        negotiable: item.negotiable,
        contractReady: item.contract_ready,
        fullDocs: item.full_docs,
        transactionType: item.transaction_type,
        description: item.description,
        views: item.views,
        contacts: item.contacts,
        createdAtLabel: item.created_at_label,
        seller,
      };
    },

    _formatJoinDate(dateStr) {
      if (!dateStr) return 'سال پیش';
      try {
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now - date;
        const diffYears = Math.floor(diffMs / (365 * 24 * 60 * 60 * 1000));
        
        if (diffYears >= 1) {
          return `۱۴${(1402 - diffYears).toString().slice(-2)}`;
        }
        return 'امسال';
      } catch (e) {
        return 'سال پیش';
      }
    },

    demoPool() {
      const banks = [
        { name: 'بانک ملی', plan: 'مهربانی', color: 'bg-emerald-500' },
        { name: 'بانک ملت', plan: 'طرح رفاه ملت', color: 'bg-red-400' },
        { name: 'بانک رسالت', plan: 'قرض‌الحسنه', color: 'bg-sky-500' },
        { name: 'بانک صادرات', plan: 'طرح صادرات کارت', color: 'bg-blue-400' },
        { name: 'بانک سامان', plan: 'طرح سامان کارت', color: 'bg-amber-400' },
        { name: 'بانک پارسیان', plan: 'طرح پارسیان پلاس', color: 'bg-purple-400' },
        { name: 'بانک آینده', plan: 'طرح آینده‌سازان', color: 'bg-teal-500' },
        { name: 'بانک مسکن', plan: 'وام ودیعه مسکن', color: 'bg-rose-400' },
      ];
      const cities = [
        { p: 'تهران', c: 'تهران' }, { p: 'اصفهان', c: 'اصفهان' }, { p: 'فارس', c: 'شیراز' },
        { p: 'خراسان رضوی', c: 'مشهد' }, { p: 'آذربایجان شرقی', c: 'تبریز' }, { p: 'البرز', c: 'کرج' },
      ];
      const sellers = [
        { id: 1, name: 'محمد رضایی', rating: 4.8, verified: true, vip: true, activeAdsCount: 12, joinedAt: '۱۴۰۲' },
        { id: 2, name: 'سارا احمدی', rating: 4.2, verified: true, vip: false, activeAdsCount: 6, joinedAt: '۱۴۰۱' },
        { id: 3, name: 'علی کریمی', rating: 3.9, verified: false, vip: false, activeAdsCount: 3, joinedAt: '۱۴۰۳' },
        { id: 4, name: 'زهرا موسوی', rating: 5.0, verified: true, vip: true, activeAdsCount: 20, joinedAt: '۱۴۰۰' },
      ];

      const items = [];
      for (let i = 1; i <= 42; i++) {
        const bank = banks[i % banks.length];
        const loc = cities[i % cities.length];
        const seller = sellers[i % sellers.length];
        items.push({
          id: i,
          sellerId: seller.id,
          seller,
          title: `وام ${['مسکن', 'خودرو', 'شخصی', 'کسب و کار'][i % 4]} - ${bank.name}`,
          plan: bank.plan,
          bankName: bank.name,
          bankInitial: bank.name[4] || bank.name[0],
          color: bank.color,
          loanAmount: 50000000 + (i % 10) * 90000000,
          scorePrice: 5000000 + (i % 12) * 24000000,
          profitRate: (i % 7) * 4,
          repaymentMonths: [10, 12, 18, 24, 36, 48, 60][i % 7],
          installmentAmount: 500000 + (i % 10) * 4500000,
          province: loc.p,
          city: loc.c,
          urgent: i % 5 === 0,
          vip: i % 4 === 0,
          guaranteed: i % 6 === 0,
          transactionType: ['escrow', 'vip_no_escrow', 'in_person', 'online'][i % 4],
          negotiable: i % 3 === 0,
          contractReady: i % 2 === 0,
          fullDocs: i % 2 === 1,
          description: `این آگهی مربوط به امتیاز وام ${['مسکن', 'خودرو', 'شخصی', 'کسب و کار'][i % 4]} از ${bank.name} با طرح ${bank.plan} است. امتیاز این وام به‌صورت کامل و قانونی قابل انتقال بوده و از طریق مراجع رسمی بانک به خریدار منتقل می‌شود. فروشنده آماده مذاکره درباره شرایط پرداخت و زمان انتقال است.`,
          views: 80 + i * 7,
          contacts: 3 + (i % 15),
          createdAtLabel: ['امروز', 'دیروز', '۳ روز پیش', '۱ هفته پیش'][i % 4],
        });
      }
      return items;
    },
  };
}
</script>

</body>
</html>
