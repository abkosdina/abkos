// ====================== CONFIG & STATIC DATA ======================
const STATIC_DATA = {
  phoneListings: [
    { id: 1, title: 'وام مسکن - بانک ملت', score: 850, price: 120000000, color: 'bg-red-400' },
    { id: 2, title: 'وام خودرو - بانک صادرات', score: 720, price: 85000000, color: 'bg-blue-400' },
    { id: 3, title: 'وام شخصی - بانک ملی', score: 810, price: 65000000, color: 'bg-amber-400' },
  ],

  steps: [
    { n: 1, title: 'ثبت نام', desc: 'ثبت نام به عنوان سرمایه گذار یا متقاضی را انتخاب کنید', icon: '...' },
    { n: 2, title: 'انتخاب امتیاز', desc: 'امتیاز وام مورد نظر خود را انتخاب کنید', icon: '...' },
    { n: 3, title: 'تماس و توافق', desc: 'با فروشنده تماس بگیرید و شرایط را توافق کنید', icon: '...' },
    { n: 4, title: 'انتقال رسمی', desc: 'انتقال امتیاز از طریق مراجع رسمی بانک', icon: '...' },
    { n: 5, title: 'دریافت وام', desc: 'با امتیاز بالاتر، سریع‌تر وام خود را دریافت کنید', icon: '...' },
  ],

  whyUs: [
    { title: 'شفافیت کامل', desc: 'تمامی معاملات شفاف و قابل پیگیری', icon: '...' },
    { title: 'انتقال امن', desc: 'انتقال امتیاز از طریق مراجع رسمی بانک', icon: '...' },
    { title: 'تنوع بالا', desc: 'دسترسی به امتیازهای وام مختلف از بانک‌های مختلف', icon: '...' },
    { title: 'صرفه جویی زمان', desc: 'دریافت وام سریع‌تر با امتیاز بالاتر', icon: '...' },
    { title: 'پشتیبانی ۲۴/۷', desc: 'پشتیبانی کامل در تمام مراحل معامله', icon: '...' },
  ],

  banks: [
    { id: 1, name: 'بانک ملت', initial: 'م', color: 'bg-red-50 text-red-600' },
    { id: 2, name: 'بانک صادرات ایران', initial: 'ص', color: 'bg-blue-50 text-blue-600' },
    { id: 3, name: 'بانک ملی ایران', initial: 'ملی', color: 'bg-emerald-50 text-emerald-600' },
    { id: 4, name: 'بانک پارسیان', initial: 'پ', color: 'bg-purple-50 text-purple-600' },
    { id: 5, name: 'بانک سامان', initial: 'س', color: 'bg-amber-50 text-amber-600' },
    { id: 6, name: 'بانک آینده', initial: 'آ', color: 'bg-sky-50 text-sky-600' },
  ],

  counterTargets: {
    ads: 12450,
    deals: 8250,
    banks: 45,
    volume: 1250,
  },
};

// Ensure axios uses same origin and includes cookies for Sanctum
if (typeof axios !== 'undefined') {
  axios.defaults.withCredentials = true;
  axios.defaults.baseURL = window.location.origin;
  const __token = localStorage.getItem('auth_token') || localStorage.getItem('token') || localStorage.getItem('access_token');
  if (__token) axios.defaults.headers.common['Authorization'] = `Bearer ${__token}`;
}

// ====================== MAIN APP ======================
function siteApp() {
  return {
    // State
    mobileMenu: false,
    loadingListings: true,
    listings: [],
    isAuthenticated: false,
    user: null,
    isAdModalOpen: false,
    adForm: {
      title: '',
      description: '',
      province_id: '',
      city_id: '',
      loan_offer: {
        bank_id: null,
        loan_plan_id: null,
        loan_amount: null,
        sale_price: null,
        installment_count: null,
        repayment_method: null,
      },
    },

    counters: {
      ads: 0,
      deals: 0,
      banks: 0,
      volume: 0,
    },

    // Static Data
    phoneListings: STATIC_DATA.phoneListings,
    steps: STATIC_DATA.steps,
    whyUs: STATIC_DATA.whyUs,
    // banks loaded from API
    banksList: [],
    bankPlans: {},

    // Locations cache
    provinces: [],
    cities: [],

    // ==================== Lifecycle ====================
    init() {
      this._applyLocalTokenIfAny();
      this.checkAuthentication();
      this.animateCounters();
      this.fetchLatestListings();
      this.loadBanks();
    },

    // ==================== Auth ====================
    _applyLocalTokenIfAny() {
      // common token keys we might store locally
      const keys = ['sanctum_token', 'auth_token', 'token', 'access_token', 'user_authenticated'];
      for (const k of keys) {
        const v = localStorage.getItem(k);
        if (v) {
          if (k === 'user_authenticated' && (v === '1' || v === 'true')) {
            this.isAuthenticated = true;
            continue;
          }
          // delegate to central setter so UI and storage stay in sync
          this.setAuthToken(v);
          break;
        }
      }
    },

    async checkAuthentication() {
      try {
        const { data } = await axios.get('/api/v1/auth/me', {
          headers: { Accept: 'application/json' },
        });

        this.isAuthenticated = !!(data?.user?.id) || this.isAuthenticated;
        this.user = data?.user ?? this.user;
        // store a lightweight marker for other scripts
        if (this.isAuthenticated) localStorage.setItem('user_authenticated', '1');
      } catch (err) {
        // if the server rejects (401/403) clear local token and state
        const status = err?.response?.status;
        if (status === 401 || status === 403) {
          this.clearAuthToken();
        } else if (!this.isAuthenticated) {
          this.isAuthenticated = false;
          this.user = null;
          localStorage.removeItem('user_authenticated');
        }
      }
    },

    // Set or clear auth token centrally
    setAuthToken(token) {
      if (token) {
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user_authenticated', '1');
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        this.isAuthenticated = true;
        return;
      }
      this.clearAuthToken();
    },

    clearAuthToken() {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_authenticated');
      delete axios.defaults.headers.common['Authorization'];
      this.isAuthenticated = false;
      this.user = null;
    },

    // ==================== Navigation ====================
    navigation: {
      dashboard: () => (window.location.href = '/dashboard'),
      login: () => (window.location.href = '/users/login'),
      register: () => (window.location.href = '/users/register'),
      logout: () => this.logout(),
      loans: () => (window.location.href = '/ads/loadLoans'),
      blog: () => (window.location.href = '/'),
    },

    async logout() {
      try {
        await axios.post('/api/v1/auth/logout');
      } catch (err) {
        // ignore server errors and still clear local state
      }
      this.clearAuthToken();
      window.location.href = '/';
    },

    goToGuide() {
      document.getElementById('how-it-works')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    goToAbout() {
      document.getElementById('why-us')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    goToContact() {
      document.getElementById('cta')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    goToLoans() {
      this.navigation.loans();
    },

    openAdModal() {
      if (!this.isAuthenticated) return this.navigation.login();
      this.isAdModalOpen = true;
      this.adForm.province_id = '';
      this.adForm.city_id = '';
      this.cities = [];
      // load provinces from API when modal opens
      this.loadProvinces(true);
    },

    async loadBanks() {
      const cacheKey = 'app:banks_v1';
      const cached = this._getCached(cacheKey);
      if (cached) {
        this.banksList = cached.banks || [];
        this.bankPlans = cached.bank_plans || {};
        return;
      }

      try {
        const { data } = await axios.get('/api/v1/banks', { headers: { Accept: 'application/json' } });
        this.banksList = Array.isArray(data.banks) ? data.banks : [];
        this.bankPlans = data.bank_plans || {};
        this._setCached(cacheKey, { banks: this.banksList, bank_plans: this.bankPlans });
      } catch (err) {
        console.error('Failed to load banks', err);
        this.banksList = [];
        this.bankPlans = {};
      }
    },

    // when bank select changes, reset selected loan plan
    onBankChange(bankId) {
      this.adForm.loan_offer.loan_plan_id = null;
    },

    get plansForSelectedBank() {
      const bankId = this.adForm.loan_offer.bank_id;
      if (!bankId) return [];
      // banksList items use numeric id, bankPlans keys are bank codes
      const bank = this.banksList.find((b) => b.id === Number(bankId) || String(b.id) === String(bankId));
      const bankKey = bank?.code ?? String(bankId);
      const plans = Array.isArray(this.bankPlans?.[bankKey]) ? this.bankPlans[bankKey] : [];
      return plans;
    },

    // compute price per million toman (inputs entered in millions)
    computePricePerMillion() {
      const loanMillions = Number(this.normalizeMoneyValue(this.adForm.loan_offer.loan_amount)) || 0;
      const priceMillions = Number(this.normalizeMoneyValue(this.adForm.loan_offer.sale_price)) || 0;
      if (loanMillions <= 0) return 0;
      const pricePerMillionToman = (priceMillions * 1000000) / loanMillions;
      return Math.round(pricePerMillionToman);
    },

    // ==================== Locations (provinces/cities) ====================
    _getCached(key, maxAgeMs = 24 * 60 * 60 * 1000) {
      try {
        const raw = localStorage.getItem(key);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        if (!parsed || !parsed.ts || !parsed.data) return null;
        if (Date.now() - parsed.ts > maxAgeMs) return null;
        return parsed.data;
      } catch (e) {
        return null;
      }
    },

    _setCached(key, data) {
      try {
        localStorage.setItem(key, JSON.stringify({ ts: Date.now(), data }));
      } catch (e) {
        // ignore storage errors
      }
    },

    async loadProvinces(force = false) {
      const cacheKey = 'app:provinces_v1';
      const cached = this._getCached(cacheKey);
      if (cached && !force) {
        this.provinces = cached;
        return cached;
      }

      try {
        const { data } = await axios.get('/api/v1/locations/provinces', { headers: { Accept: 'application/json' } });
        const list = data?.data ?? data ?? [];
        this.provinces = list;
        this._setCached(cacheKey, list);
        return list;
      } catch (err) {
        console.error('Failed to load provinces', err);
        this.provinces = [];
        return [];
      }
    },

    async fetchCitiesForProvince(provinceId, force = false) {
      this.cities = [];
      this.adForm.city_id = '';
      if (!provinceId) return;

      const cacheKey = `app:cities_v1:${provinceId}`;
      const cached = this._getCached(cacheKey);
      if (cached && !force) {
        this.cities = cached;
        return cached;
      }

      try {
        const { data } = await axios.get(`/api/v1/locations/provinces/${provinceId}/cities`, { headers: { Accept: 'application/json' } });
        const list = data?.data ?? data ?? [];
        this.cities = list;
        this._setCached(cacheKey, list);
        return list;
      } catch (err) {
        console.error('Failed to load cities for province', provinceId, err);
        this.cities = [];
        return [];
      }
    },

    async submitAd() {
      // basic client-side validation
      if (!this.adForm.title || !this.adForm.description) {
        alert('عنوان و توضیحات الزامی هستند');
        return;
      }

      // require bank, plan, loan amount and sale price
      if (!this.adForm.loan_offer.bank_id || !this.adForm.loan_offer.loan_plan_id) {
        alert('لطفاً بانک و طرح را انتخاب کنید');
        return;
      }

      if (!this.adForm.loan_offer.loan_amount || !this.adForm.loan_offer.sale_price) {
        alert('لطفاً میزان امتیاز و قیمت درخواستی را وارد کنید');
        return;
      }

      try {
        // prepare loan_offer payload enriched with plan defaults (read-only from admin)
        let loanOfferPayload = null;
        if (this.adForm.loan_offer && this.adForm.loan_offer.bank_id && this.adForm.loan_offer.loan_plan_id) {
          const selectedPlan = this.plansForSelectedBank.find((p) => String(p.id) === String(this.adForm.loan_offer.loan_plan_id));
          const bankId = Number(this.adForm.loan_offer.bank_id);
          const planId = Number(this.adForm.loan_offer.loan_plan_id);
          const loanAmountValue = this.toStorageAmount(this.adForm.loan_offer.loan_amount);
          const salePriceValue = this.toStorageAmount(this.adForm.loan_offer.sale_price);
          const installmentCount = selectedPlan?.installment_count ?? (Number(this.adForm.loan_offer.installment_count) || 0);
          const durationMonths = selectedPlan?.duration_months ?? (Number(this.adForm.loan_offer.duration_months) || 0);
          const interestRate = selectedPlan?.interest_rate ?? this.adForm.loan_offer.interest_rate;

          loanOfferPayload = {
            ...this.adForm.loan_offer,
            bank_id: bankId,
            loan_plan_id: planId,
            loan_amount: loanAmountValue,
            sale_price: salePriceValue,
            loan_type_id: Number(selectedPlan?.loan_type_id ?? 1),
            duration_months: Number(durationMonths),
            installment_count: Number(installmentCount),
          };

          if (interestRate !== undefined && interestRate !== null) {
            loanOfferPayload.interest_rate = String(interestRate);
          }

          if (installmentCount > 0 && Number(salePriceValue) > 0) {
            loanOfferPayload.monthly_installment = String(Math.round(Number(salePriceValue) / installmentCount));
          }
        }

        const payload = {
          title: this.adForm.title,
          description: this.adForm.description,
          province_id: this.adForm.province_id ? Number(this.adForm.province_id) : undefined,
          city_id: this.adForm.city_id ? Number(this.adForm.city_id) : undefined,
          visibility: 'Public',
          priority: 0,
          ...(loanOfferPayload ? { loan_offer: loanOfferPayload } : {}),
        };

        const { data } = await axios.post('/api/advertisements', payload);
        const adId = data?.data?.advertisement?.id ?? data?.data?.advertisement_id ?? data?.data?.advertisement?.advertisement_id ?? null;
        // fallback: if response returns advertisement object
        const createdAd = data?.data?.advertisement ?? data?.data ?? null;

        // attempt to submit for review
        const newAdId = createdAd?.id ?? createdAd?.advertisement_id ?? adId;
        if (newAdId) {
          await axios.post(`/api/advertisements/${newAdId}/submit`);
        }

        this.isAdModalOpen = false;
        alert('آگهی شما ثبت شد و پس از بررسی منتشر خواهد شد.');
        // optionally refresh listings or redirect to dashboard
        this.fetchLatestListings();
      } catch (err) {
        console.error('Failed to submit ad', err);
        const msg = err?.response?.data?.message || err?.message || 'خطا در ثبت آگهی';
        alert(msg);
      }
    },

    goToRegister() {
      if (this.isAuthenticated) return this.navigation.dashboard();
      return this.navigation.register();
    },

    goToLogin() {
      if (this.isAuthenticated) return this.navigation.dashboard();
      return this.navigation.login();
    },

    goToDashboard() { return this.navigation.dashboard(); },

    // ==================== Utils ====================
    normalizeMoneyValue(value) {
      if (value === null || value === undefined) return '';
      return String(value).replace(/[^0-9.-]/g, '');
    },

    toStorageAmount(value) {
      const raw = this.normalizeMoneyValue(value);
      if (!raw) return '0';
      const numeric = Number(raw);
      if (!Number.isFinite(numeric) || numeric <= 0) return '0';
      const scaled = numeric >= 1000000000 ? numeric : numeric * 1000000;
      const bounded = Math.min(Math.max(scaled, 0), 999999999999999.99);
      const rounded = Math.round(bounded * 100) / 100;
      return String(rounded.toFixed(2)).replace(/\.00$/, '');
    },

    formatMoneyInput(value) {
      const digits = this.normalizeMoneyValue(value);
      if (digits === '') return '';
      return Number(digits).toLocaleString('en-US');
    },

    formatToman(n) {
      return Number(n).toLocaleString('en-US');
    },

    // ==================== Animations ====================
    animateCounters() {
      const { counterTargets } = STATIC_DATA;
      const duration = 1400;
      const startTime = performance.now();

      const animate = (now) => {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic

        this.counters.ads = Math.floor(counterTargets.ads * eased);
        this.counters.deals = Math.floor(counterTargets.deals * eased);
        this.counters.banks = Math.floor(counterTargets.banks * eased);
        this.counters.volume = Math.floor(counterTargets.volume * eased);

        if (progress < 1) requestAnimationFrame(animate);
      };

      requestAnimationFrame(animate);
    },

    // ==================== API ====================
    async fetchLatestListings() {
      this.loadingListings = true;

      try {
        const { data } = await axios.get('/api/advertisements', {
          params: { page: 1, per_page: 3, sort: 'newest', ad_status: ['vip'] },
          headers: { Accept: 'application/json' },
        });

        const items = data?.data ?? data ?? [];
        this.listings = Array.isArray(items) ? items.map(this.mapListing) : [];
      } catch (err) {
        console.error('خطا در دریافت آگهی‌های صفحه اصلی:', err);
        this.listings = [];
      } finally {
        this.loadingListings = false;
      }
    },

    mapListing(item) {
      // Prefer explicit bank name fields returned by the API. Support multiple shapes.
      const bankName = (item.bank_name)
        || (item.loan_offer && item.loan_offer.bank_name)
        || (item.loan_offer && item.loan_offer.bank && (item.loan_offer.bank.name || item.loan_offer.bank.title))
        || (item.bank && (item.bank.name || item.bank.title))
        || '';

      const computeInitials = (name) => {
        if (!name) return '?';
        const parts = name.trim().split(/\s+/).filter(Boolean);
        if (parts.length === 1) return parts[0].slice(0, 1);
        return (parts[0].slice(0, 1) + parts[parts.length - 1].slice(0, 1)).toUpperCase();
      };

      return {
        id: item.id,
        title: item.title || 'آگهی',
        plan: item.plan || item.loan_plan || 'طرح ویژه',
        bankName: bankName || (item.title || ''),
        bankInitial: computeInitials(bankName || item.title || '?'),
        score: item.seller?.rating ? Number(item.seller.rating).toFixed(1) : (item.priority_label || '0'),
        price: item.price ?? item.score_price ?? 0,
        city: item.city || item.city_id || 'نامشخص',
        color: ['bg-red-400', 'bg-blue-400', 'bg-amber-400', 'bg-emerald-500', 'bg-purple-400'][item.id % 5],
      };
    },
  };
}

window.siteApp = siteApp;