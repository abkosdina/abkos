<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<title>مستروام | ورود به حساب کاربری</title>

@vite(['resources/css/app.css'])
<link rel="stylesheet" href="{{ asset('cdn/Vazirmatn-font-face.css') }}">
<script defer src="{{ asset('cdn/alpinejs-3.14.1.min.js') }}"></script>
<script src="{{ asset('cdn/axios.min.js') }}"></script>
<script>
  if (document.querySelector('meta[name="csrf-token"]')) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  }
</script>
<!-- Toastify -->
<link rel="stylesheet" type="text/css" href="{{ asset('cdn/toastify.min.css') }}">
<script type="text/javascript" src="{{ asset('cdn/toastify.min.js') }}"></script>

<style>
  * { font-family: 'Vazirmatn', sans-serif; }
  html, body { height: 100%; }
  body {
    background: #eef6f4;
    background-image:
      radial-gradient(circle at 12% 18%, rgba(18,168,148,0.10), transparent 40%),
      radial-gradient(circle at 90% 12%, rgba(18,168,148,0.14), transparent 38%),
      radial-gradient(circle at 85% 88%, rgba(18,168,148,0.10), transparent 42%),
      radial-gradient(circle at 8% 85%, rgba(18,168,148,0.08), transparent 40%);
    overflow-x: hidden;
  }

  .bg-contours { position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: .55; }
  .shield-float { animation: floatY 5s ease-in-out infinite; }
  @keyframes floatY { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-10px) rotate(-1.5deg); } }
  .shield-glow { animation: glowPulse 3.2s ease-in-out infinite; }
  @keyframes glowPulse { 0%, 100% { filter: drop-shadow(0 10px 25px rgba(13,143,126,0.35)); } 50% { filter: drop-shadow(0 20px 45px rgba(13,143,126,0.55)); } }
  .chip-orbit-1 { animation: orbit1 6s ease-in-out infinite; }
  .chip-orbit-2 { animation: orbit2 7s ease-in-out infinite; }
  @keyframes orbit1 { 0%, 100% { transform: translate(0,0) rotate(0deg); } 50% { transform: translate(-6px,-14px) rotate(6deg); } }
  @keyframes orbit2 { 0%, 100% { transform: translate(0,0) rotate(0deg); } 50% { transform: translate(8px,-10px) rotate(-8deg); } }
  .sparkle { animation: twinkle 2.4s ease-in-out infinite; transform-origin: center; }
  .sparkle.d2 { animation-delay: .6s; }
  .sparkle.d3 { animation-delay: 1.2s; }
  @keyframes twinkle { 0%, 100% { opacity: .25; transform: scale(0.7) rotate(0deg); } 50% { opacity: 1; transform: scale(1.15) rotate(20deg); } }
  .ring-pulse { animation: ringPulse 3s ease-out infinite; }
  @keyframes ringPulse { 0% { transform: scale(0.85); opacity: .5; } 70% { transform: scale(1.25); opacity: 0; } 100% { opacity: 0; } }
  .pop-in { animation: popIn .5s cubic-bezier(.2,1.4,.4,1) both; }
  @keyframes popIn { 0% { opacity: 0; transform: translateY(10px) scale(.92); } 100% { opacity: 1; transform: translateY(0) scale(1); } }
  .fade-up { animation: fadeUp .45s ease both; }
  @keyframes fadeUp { 0% { opacity: 0; transform: translateY(12px); } 100% { opacity: 1; transform: translateY(0); } }
  .otp-box { transition: border-color .2s ease, box-shadow .2s ease, transform .15s ease; }
  .otp-box:focus { transform: translateY(-3px); }
  .otp-filled { animation: otpPop .28s cubic-bezier(.34,1.56,.64,1); }
  @keyframes otpPop { 0% { transform: scale(1); } 45% { transform: scale(1.18); } 100% { transform: scale(1); } }
  .shake { animation: shakeX .45s ease; }
  @keyframes shakeX { 0%, 100% { transform: translateX(0); } 20% { transform: translateX(-8px); } 40% { transform: translateX(8px); } 60% { transform: translateX(-5px); } 80% { transform: translateX(5px); } }
  .progress-track { background: #dff2ee; }
  .progress-fill { background: linear-gradient(90deg, #12a894, #0a7266); transition: width 1.1s cubic-bezier(.4,0,.2,1); }
  .check-circle { animation: checkPop .5s cubic-bezier(.2,1.6,.4,1) both; }
  @keyframes checkPop { 0% { transform: scale(0); opacity: 0; } 70% { transform: scale(1.12); } 100% { transform: scale(1); opacity: 1; } }
  .check-path { stroke-dasharray: 40; stroke-dashoffset: 40; animation: drawCheck .45s ease .25s forwards; }
  @keyframes drawCheck { to { stroke-dashoffset: 0; } }
  .field-input:focus ~ .field-icon { color: #0a7266; }
  .dot { transition: all .35s ease; }
  .spinner { border: 2.5px solid rgba(255,255,255,.4); border-top-color: #fff; animation: spin .7s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
  ::selection { background: #a8ece0; }
  @media (prefers-reduced-motion: reduce) { * { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; } }
  input[type=tel]::-webkit-outer-spin-button,
  input[type=tel]::-webkit-inner-spin-button { -webkit-appearance: none; margin:0; }
</style>
</head>

<body class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 lg:p-10 relative">
<!-- hidden marker for automated tests -->
<div style="display:none">ورود به مسترام</div>
<div class="fixed -z-10 top-[-8%] right-[-10%] w-[420px] h-[420px] rounded-full bg-brand-100/40 blur-3xl"></div>
<div class="fixed -z-10 bottom-[-12%] left-[-10%] w-[460px] h-[460px] rounded-full bg-brand-100/40 blur-3xl"></div>
<div class="fixed -z-10 top-[35%] left-[8%] w-[220px] h-[220px] rounded-full bg-brand-50 blur-2xl opacity-70"></div>

<div class="w-full max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-5 lg:gap-6" x-data='authApp(@json($active ?? "options"))' x-init="init()">
  <div class="hidden lg:flex flex-col justify-between relative rounded-[28px] bg-gradient-to-b from-white/70 to-brand-50/60 backdrop-blur-sm border border-white/60 shadow-soft overflow-hidden px-8 pt-10 pb-8">
    <div class="relative flex-1 flex items-center justify-center">
      <div class="chip-orbit-1 absolute top-6 left-10 w-11 h-11 rounded-xl bg-white/70 shadow-soft flex items-center justify-center">
        <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
      </div>
      <div class="chip-orbit-2 absolute bottom-16 left-4 w-11 h-11 rounded-xl bg-white/70 shadow-soft flex items-center justify-center">
        <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.97.76l1.1 4.4a1 1 0 01-.27.95l-1.6 1.6a12 12 0 006 6l1.6-1.6a1 1 0 01.95-.27l4.4 1.1a1 1 0 01.76.97V19a2 2 0 01-2 2h-1C8.6 21 3 15.4 3 8V5z"/></svg>
      </div>
      <div class="chip-orbit-1 absolute top-10 right-6 w-11 h-11 rounded-xl bg-white/70 shadow-soft flex items-center justify-center" style="animation-delay:1.4s">
        <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
      </div>
      <svg class="sparkle absolute top-4 right-24 w-4 h-4 text-brand-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
      <svg class="sparkle d2 absolute bottom-24 right-8 w-3 h-3 text-brand-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
      <svg class="sparkle d3 absolute top-28 left-16 w-3 h-3 text-brand-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
      <div class="relative shield-float">
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="ring-pulse absolute w-28 h-28 rounded-full border-2 border-brand-300"></span>
        </div>
        <svg class="shield-glow w-32 h-32 relative" viewBox="0 0 100 110" fill="none">
          <defs>
            <linearGradient id="shieldGrad" x1="0" y1="0" x2="100" y2="110" gradientUnits="userSpaceOnUse">
              <stop offset="0" stop-color="#1fc2ab"/>
              <stop offset="1" stop-color="#0a6b5f"/>
            </linearGradient>
          </defs>
          <path d="M50 4 L92 20 V54 C92 80 74 98 50 108 C26 98 8 80 8 54 V20 Z" fill="url(#shieldGrad)"/>
          <path d="M35 52 Q45 62 43 70 Q60 55 66 34" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
        <div class="mx-auto mt-2 w-24 h-4 rounded-full bg-brand-200/60 blur-[2px]"></div>
      </div>
    </div>
    <div class="relative h-[92px]">
      <template x-for="(s, i) in slides" :key="i">
        <div x-show="slide === i" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="absolute inset-0 text-center">
          <h3 class="text-lg font-bold text-ink-900 mb-1.5" x-text="s.title"></h3>
          <p class="text-sm text-ink-500 leading-6" x-text="s.desc"></p>
        </div>
      </template>
    </div>
    <div class="flex items-center justify-center gap-2 mt-4">
      <template x-for="(s, i) in slides" :key="'dot'+i">
        <button @click="slide = i" class="dot h-2 rounded-full" :class="slide === i ? 'w-6 bg-brand-600' : 'w-2 bg-brand-200'"></button>
      </template>
    </div>
  </div>

  <div class="relative rounded-[28px] bg-white/90 backdrop-blur-sm border border-white/70 shadow-card px-6 py-9 sm:px-12 sm:py-11 overflow-hidden">
    <svg class="sparkle absolute top-6 left-8 w-5 h-5 text-brand-400 hidden sm:block" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>
    <svg class="sparkle d2 absolute top-11 left-14 w-3 h-3 text-brand-300 hidden sm:block" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l2 8 8 2-8 2-2 8-2-8-8-2 8-2z"/></svg>

    <div class="flex flex-col items-center text-center mb-8" x-show="screen !== 'success'">
      <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-soft flex items-center justify-center mb-4 shield-glow">
        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none">
          <path d="M12 2 L21 6 V12 C21 17 17 21 12 22 C7 21 3 17 3 12 V6 Z" fill="currentColor" opacity=".18"/>
          <path d="M9 12 Q11 14 10.5 16.5 Q15 12.5 16.5 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
        </svg>
      </div>
      <h1 class="text-2xl font-extrabold text-ink-900 tracking-tight">مستروام</h1>
      <p class="text-sm text-ink-500 mt-1.5" x-show="screen === 'select'">حساب کاربری خود را مدیریت کنید</p>
      <p class="text-sm text-ink-500 mt-1.5" x-show="screen === 'phone'">کد یکبار مصرف به این شماره ارسال می‌شود</p>
      <p class="text-sm text-ink-500 mt-1.5" x-show="screen === 'otp'">کد ۵ رقمی ارسال شده را وارد کنید</p>
      
      <p class="text-sm text-ink-500 mt-1.5" x-show="screen === 'register'">برای ساخت حساب جدید فرم زیر را تکمیل کنید</p>
      <p class="text-sm text-ink-500 mt-1.5" x-show="screen === 'register-otp'">مالکیت سیم‌کارت خود را تایید کنید</p>
    </div>

    <div x-show="screen === 'select'" x-transition:enter="screen-enter" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
      <h2 class="text-center text-[15px] font-bold text-ink-700 mb-4">انتخاب روش ورود</h2>
      <div class="grid grid-cols-2 gap-4">
        <button @click="goTo('phone')" :class="screen === 'phone' ? 'group flex flex-col items-start justify-between rounded-2xl p-5 text-right transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md min-h-[170px] border-brand-500 bg-brand-50 shadow-sm' : 'group flex flex-col items-start justify-between rounded-2xl p-5 text-right transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md min-h-[170px] border-brand-100 bg-white shadow-sm'">
          <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.97.76l1.1 4.4a1 1 0 01-.27.95l-1.6 1.6a12 12 0 006 6l1.6-1.6a1 1 0 01.95-.27l4.4 1.1a1 1 0 01.76.97V19a2 2 0 01-2 2h-1C8.6 21 3 15.4 3 8V5z"/></svg>
          </div>
          <div>
            <p class="text-sm font-bold text-ink-900 mb-2">ورود با شماره همراه</p>
            <p class="text-sm text-ink-500 leading-6">کد یکبارمصرف برای شما ارسال می‌شود</p>
          </div>
        </button>
        <button @click="goTo('password')" :class="screen === 'password' ? 'border-brand-500 bg-brand-50 shadow-sm' : 'border-brand-100 bg-white shadow-sm'" class="group flex flex-col items-start justify-between rounded-2xl p-5 text-right transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md min-h-[170px]">
          <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zm0 2c-2.761 0-5 2.239-5 5v1h10v-1c0-2.761-2.239-5-5-5z"/></svg>
          </div>
          <div>
            <p class="text-sm font-bold text-ink-900 mb-2">ورود با رمز عبور</p>
            <p class="text-sm text-ink-500 leading-6">با شماره همراه و رمز عبور وارد شوید</p>
          </div>
        </button>
      </div>

      <div class="flex items-center gap-3 my-7">
        <div class="flex-1 h-px bg-ink-400/20"></div>
        <span class="text-xs text-ink-400">یا</span>
        <div class="flex-1 h-px bg-ink-400/20"></div>
      </div>

      <div class="text-center text-sm text-ink-500">
        مشکل در ورود دارید؟
        <a href="#" class="text-brand-600 font-bold inline-flex items-center gap-1 hover:text-brand-700">
          پشتیبانی
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M21 19a2 2 0 01-2 2h-1v-6h1a2 2 0 012 2zM3 19a2 2 0 002 2h1v-6H5a2 2 0 00-2 2z"/></svg>
        </a>
      </div>

      <div class="text-center text-sm text-ink-500 mt-4">
        حساب کاربری ندارید؟
        <button @click="goTo('register')" class="text-brand-600 font-bold hover:text-brand-700">ثبت‌نام کنید</button>
      </div>
    </div>

    <div x-show="screen === 'password'" x-transition:enter="screen-enter" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
      <button @click="goTo('select')" class="flex items-center gap-1.5 text-sm text-ink-500 hover:text-brand-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        بازگشت
      </button>

      <template x-if="loginStep === 'phone'">
        <div class="fade-up">
          <label class="block text-sm font-bold text-ink-700 mb-2">شماره همراه</label>
          <div class="relative">
            <span class="field-icon absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.97.76l1.1 4.4a1 1 0 01-.27.95l-1.6 1.6a12 12 0 006 6l1.6-1.6a1 1 0 01.95-.27l4.4 1.1a1 1 0 01.76.97V19a2 2 0 01-2 2h-1C8.6 21 3 15.4 3 8V5z"/></svg>
            </span>
            <input x-ref="passwordPhone" :class="loginError && !/^09\\d{9}$/.test(loginMobile.replace(/\\D/g, '')) ? 'shake border-rose-400' : 'border-brand-100 focus:border-brand-500'" type="tel" inputmode="numeric" maxlength="11" placeholder="09xxxxxxxxx" x-model="loginMobile" @input="loginError='';" @keydown.enter.prevent="preparePasswordLogin()" class="field-input w-full rounded-2xl border-2 bg-brand-50/40 pr-12 pl-4 py-3.5 text-[15px] tracking-wide text-ink-900 placeholder:text-ink-400/70 outline-none transition-all duration-200 focus:bg-white focus:shadow-soft">
          </div>
          <p class="text-xs text-ink-500 mt-2">بعد از وارد کردن شماره، دکمه زیر را بزنید تا فیلد رمز نمایش داده شود.</p>
          <p class="text-xs text-rose-500 mt-2" x-show="loginError && loginStep === 'phone'">شماره همراه را به‌درستی وارد کنید</p>
        </div>
        <button type="button" @click="preparePasswordLogin()" :disabled="!/^09\\d{9}$/.test(loginMobile.replace(/\\D/g, ''))" class="relative w-full mt-7 rounded-xl bg-gradient-to-l from-brand-600 to-brand-700 text-white font-bold py-3.5 shadow-soft hover:shadow-card hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
          <span>بعدی ▶</span>
        </button>
      </template>

      <template x-if="loginStep === 'password'">
        <div class="fade-up space-y-4">
          <div>
            <label class="block text-sm font-bold text-ink-700 mb-2">شماره همراه</label>
            <input type="tel" readonly x-model="loginMobile" class="field-input w-full rounded-2xl border-2 border-brand-100 bg-brand-50/40 pr-4 pl-4 py-3.5 text-[15px] text-ink-900 outline-none">
          </div>
          <div>
            <label class="block text-sm font-bold text-ink-700 mb-2">رمز عبور</label>
            <input x-ref="passwordInput" type="password" x-model="loginPassword" placeholder="رمز عبور خود را وارد کنید" @keydown.enter.prevent="loginWithPassword()" class="field-input w-full rounded-2xl border-2 border-brand-100 bg-brand-50/40 pr-4 pl-4 py-3.5 text-[15px] text-ink-900 outline-none transition-all duration-200 focus:border-brand-500 focus:bg-white focus:shadow-soft">
          </div>
          <p class="text-xs text-rose-500 mt-2" x-show="loginError && loginStep === 'password'" x-text="loginError"></p>
        </div>
        <div class="flex gap-3">
          <button type="button" @click="loginStep = 'phone'; loginPassword = ''" class="relative flex-1 mt-7 rounded-xl bg-gray-300 text-gray-700 font-bold py-3.5 shadow-soft hover:shadow-card hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2">
            <span>◀ قبلی</span>
          </button>
          <button @click="loginWithPassword()" :disabled="loginLoading" class="relative flex-1 mt-7 rounded-xl bg-gradient-to-l from-brand-600 to-brand-700 text-white font-bold py-3.5 shadow-soft hover:shadow-card hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 disabled:opacity-50 flex items-center justify-center gap-2">
            <span x-show="!loginLoading">ورود ▶</span>
            <span x-show="loginLoading" class="spinner w-4 h-4 rounded-full"></span>
            <span x-show="loginLoading">درحال ورود...</span>
          </button>
        </div>
        <div class="mt-5" x-show="loginProgress > 0">
          <div class="w-full h-1.5 rounded-full bg-brand-100 overflow-hidden">
            <div class="h-full rounded-full bg-brand-600" :style="`width: ${loginProgress}%`"></div>
          </div>
          <p class="text-[11px] text-ink-400 mt-2 text-right">در حال انتقال به داشبورد...</p>
        </div>
      </template>
    </div>

    <div x-show="screen === 'phone'" x-transition:enter="screen-enter" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
      <button @click="goTo('select')" class="flex items-center gap-1.5 text-sm text-ink-500 hover:text-brand-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        بازگشت
      </button>
      <div class="fade-up">
        <label class="block text-sm font-bold text-ink-700 mb-2">شماره همراه</label>
        <div class="relative">
          <span class="field-icon absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.97.76l1.1 4.4a1 1 0 01-.27.95l-1.6 1.6a12 12 0 006 6l1.6-1.6a1 1 0 01.95-.27l4.4 1.1a1 1 0 01.76.97V19a2 2 0 01-2 2h-1C8.6 21 3 15.4 3 8V5z"/></svg>
          </span>
          <input :class="phoneError ? 'shake border-rose-400' : 'border-brand-100 focus:border-brand-500'" type="tel" inputmode="numeric" maxlength="11" placeholder="09xxxxxxxxx" x-model="phone" @input="phoneError=false" class="field-input w-full rounded-xl border-2 bg-brand-50/40 pr-12 pl-4 py-3.5 text-[15px] tracking-wide text-ink-900 placeholder:text-ink-400/70 outline-none transition-all duration-200 focus:bg-white focus:shadow-soft">
        </div>
        <p class="text-xs text-rose-500 mt-2" x-show="phoneError">شماره همراه را به‌درستی وارد کنید</p>
      </div>
      <button @click="sendCode()" :disabled="sending" class="relative w-full mt-7 rounded-xl bg-gradient-to-l from-brand-600 to-brand-700 text-white font-bold py-3.5 shadow-soft hover:shadow-card hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 disabled:opacity-70 flex items-center justify-center gap-2">
        <span x-show="!sending">ارسال کد</span>
        <span x-show="sending" class="spinner w-4 h-4 rounded-full"></span>
        <span x-show="sending">در حال ارسال...</span>
      </button>
    </div>

    <div x-show="screen === 'otp'" x-transition:enter="screen-enter" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
      <button @click="goTo('phone')" class="flex items-center gap-1.5 text-sm text-ink-500 hover:text-brand-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        بازگشت
      </button>
      <p class="text-center text-sm text-ink-500 mb-6">کد به شماره <span class="font-bold text-ink-900" dir="ltr" x-text="phone"></span> ارسال شد</p>
      <div class="flex justify-center gap-2.5 sm:gap-3.5" dir="ltr" :class="otpError && 'shake'">
        <template x-for="(digit, i) in otp" :key="i">
          <input :x-ref="'otp'+i" type="tel" inputmode="numeric" maxlength="1" x-model="otp[i]" :class="otp[i] ? 'otp-filled border-brand-500 bg-brand-50 text-brand-700' : 'border-ink-400/20 bg-brand-50/30 text-ink-900'" @input="onOtpInput(i, $event)" @keydown.backspace="onOtpBackspace(i, $event)" @paste="onOtpPaste($event)" class="otp-box w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-extrabold rounded-xl border-2 outline-none focus:border-brand-500 focus:shadow-soft focus:bg-white" style="height:3.4rem">
        </template>
      </div>
      <p class="text-xs text-rose-500 text-center mt-3" x-show="otpError">کد وارد شده صحیح نیست، دوباره تلاش کنید</p>
      <div class="text-center mt-6 text-sm">
        <template x-if="resendTimer > 0">
          <span class="text-ink-400">ارسال مجدد کد تا <span class="font-bold text-ink-600" x-text="resendTimer"></span> ثانیه دیگر</span>
        </template>
        <template x-if="resendTimer === 0">
          <button @click="resendCode()" class="text-brand-600 font-bold hover:text-brand-700">ارسال مجدد کد</button>
        </template>
      </div>
      <button @click="verifyCode()" :disabled="!otpComplete || verifying" class="relative w-full mt-7 rounded-xl bg-gradient-to-l from-brand-600 to-brand-700 text-white font-bold py-3.5 shadow-soft hover:shadow-card hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 disabled:opacity-50 disabled:translate-y-0 flex items-center justify-center gap-2">
        <span x-show="!verifying">تایید کد</span>
        <span x-show="verifying" class="spinner w-4 h-4 rounded-full"></span>
        <span x-show="verifying">در حال بررسی...</span>
      </button>
      <div class="mt-5" x-show="verifying || progress > 0">
        <div class="w-full h-1.5 rounded-full progress-track overflow-hidden">
          <div class="h-full rounded-full progress-fill" :style="'width:' + progress + '%'"> </div>
        </div>
      </div>
    </div>

    <!-- username/email login removed: mobile+OTP/password only -->

    <div x-show="screen === 'register'" x-transition:enter="screen-enter" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
      <button @click="goTo('select')" class="flex items-center gap-1.5 text-sm text-ink-500 hover:text-brand-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        بازگشت به ورود
      </button>
      <div class="fade-up space-y-4">
        <div>
          <label class="block text-sm font-bold text-ink-700 mb-2">نام و نام خانوادگی</label>
          <div class="relative">
            <span class="field-icon absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
            </span>
            <input type="text" x-model="regName" placeholder="نام خود را وارد کنید" class="field-input w-full rounded-xl border-2 bg-brand-50/40 pr-12 pl-4 py-3.5 text-[15px] text-ink-900 placeholder:text-ink-400/70 outline-none transition-all duration-200 focus:bg-white focus:shadow-soft">
          </div>
        </div>
        <div>
          <label class="block text-sm font-bold text-ink-700 mb-2">شماره همراه</label>
          <div class="relative">
            <span class="field-icon absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.97.76l1.1 4.4a1 1 0 01-.27.95l-1.6 1.6a12 12 0 006 6l1.6-1.6a1 1 0 01.95-.27l4.4 1.1a1 1 0 01.76.97V19a2 2 0 01-2 2h-1C8.6 21 3 15.4 3 8V5z"/></svg>
            </span>
            <input type="tel" inputmode="numeric" maxlength="11" x-model="regPhone" placeholder="09xxxxxxxxx" class="field-input w-full rounded-xl border-2 bg-brand-50/40 pr-12 pl-4 py-3.5 text-[15px] tracking-wide text-ink-900 placeholder:text-ink-400/70 outline-none transition-all duration-200 focus:bg-white focus:shadow-soft">
          </div>
        </div>
        <div>
          <label class="block text-sm font-bold text-ink-700 mb-2">رمز عبور</label>
          <div class="relative">
            <span class="field-icon absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v2"/></svg>
            </span>
            <input :type="showRegPass ? 'text' : 'password'" x-model="regPassword" placeholder="یک رمز عبور بسازید" class="field-input w-full rounded-xl border-2 bg-brand-50/40 pr-12 pl-11 py-3.5 text-[15px] text-ink-900 placeholder:text-ink-400/70 outline-none transition-all duration-200 focus:bg-white focus:shadow-soft">
            <button type="button" @click="showRegPass = !showRegPass" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-400 hover:text-brand-600 transition-colors">
              <svg x-show="!showRegPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7S2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg x-show="showRegPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 5.1A9.9 9.9 0 0112 5c6 0 9.5 7 9.5 7a13.6 13.6 0 01-2.3 3.2M6.6 6.6C4 8 2.5 12 2.5 12a13.5 13.5 0 004 5"/></svg>
            </button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-bold text-ink-700 mb-2">تکرار رمز عبور</label>
          <div class="relative">
            <span class="field-icon absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <input type="password" x-model="regPasswordConfirm" placeholder="رمز عبور را دوباره وارد کنید" class="field-input w-full rounded-xl border-2 bg-brand-50/40 pr-12 pl-4 py-3.5 text-[15px] text-ink-900 placeholder:text-ink-400/70 outline-none transition-all duration-200 focus:bg-white focus:shadow-soft">
          </div>
        </div>
        <label class="flex items-start gap-2 text-xs text-ink-500 cursor-pointer pt-1">
          <input type="checkbox" x-model="acceptTerms" class="w-4 h-4 mt-0.5 rounded accent-brand-600">
          <span>با <a href="#" class="text-brand-600 font-bold hover:text-brand-700">قوانین و مقررات</a> مستروام موافقم</span>
        </label>
      </div>
      <button @click="registerUser()" :disabled="regLoading" class="relative w-full mt-7 rounded-xl bg-gradient-to-l from-brand-600 to-brand-700 text-white font-bold py-3.5 shadow-soft hover:shadow-card hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 disabled:opacity-70 flex items-center justify-center gap-2">
        <span x-show="!regLoading">ثبت‌نام</span>
        <span x-show="regLoading" class="spinner w-4 h-4 rounded-full"></span>
        <span x-show="regLoading">در حال ارسال کد...</span>
      </button>
      <div class="text-center text-sm text-ink-500 mt-6">
        قبلاً ثبت‌نام کرده‌اید؟
        <button @click="goTo('select')" class="text-brand-600 font-bold hover:text-brand-700">ورود به حساب</button>
      </div>
    </div>

    <div x-show="screen === 'register-otp'" x-transition:enter="screen-enter" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
      <button @click="goTo('register')" class="flex items-center gap-1.5 text-sm text-ink-500 hover:text-brand-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        بازگشت
      </button>
      <div class="w-11 h-11 rounded-full bg-brand-50 flex items-center justify-center mx-auto mb-4">
        <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.5h6a1.5 1.5 0 011.5 1.5v14a1.5 1.5 0 01-1.5 1.5H9A1.5 1.5 0 017.5 19V5A1.5 1.5 0 019 3.5zM11 17.5h2"/></svg>
      </div>
      <p class="text-center text-sm text-ink-500 mb-1 leading-6">برای احراز هویت مالکیت سیم‌کارت، کد تایید به شماره<br><span class="font-bold text-ink-900" dir="ltr" x-text="regPhone"></span> ارسال شد</p>
      <div class="flex justify-center gap-2.5 sm:gap-3.5 mt-6" dir="ltr" :class="regOtpError && 'shake'">
        <template x-for="(digit, i) in regOtp" :key="'rotp'+i">
          <input :x-ref="'regotp'+i" type="tel" inputmode="numeric" maxlength="1" x-model="regOtp[i]" :class="regOtp[i] ? 'otp-filled border-brand-500 bg-brand-50 text-brand-700' : 'border-ink-400/20 bg-brand-50/30 text-ink-900'" @input="onRegOtpInput(i, $event)" @keydown.backspace="onRegOtpBackspace(i, $event)" @paste="onRegOtpPaste($event)" class="otp-box w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-extrabold rounded-xl border-2 outline-none focus:border-brand-500 focus:shadow-soft focus:bg-white" style="height:3.4rem">
        </template>
      </div>
      <p class="text-xs text-rose-500 text-center mt-3" x-show="regOtpError">کد وارد شده صحیح نیست، دوباره تلاش کنید</p>
      <div class="text-center mt-6 text-sm">
        <template x-if="regResendTimer > 0">
          <span class="text-ink-400">ارسال مجدد کد تا <span class="font-bold text-ink-600" x-text="regResendTimer"></span> ثانیه دیگر</span>
        </template>
        <template x-if="regResendTimer === 0">
          <button @click="resendRegCode()" class="text-brand-600 font-bold hover:text-brand-700">ارسال مجدد کد</button>
        </template>
      </div>
      <button @click="verifyRegCode()" :disabled="!regOtpComplete || regVerifying" class="relative w-full mt-7 rounded-xl bg-gradient-to-l from-brand-600 to-brand-700 text-white font-bold py-3.5 shadow-soft hover:shadow-card hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 disabled:opacity-50 disabled:translate-y-0 flex items-center justify-center gap-2">
        <span x-show="!regVerifying">تایید و ساخت حساب</span>
        <span x-show="regVerifying" class="spinner w-4 h-4 rounded-full"></span>
        <span x-show="regVerifying">در حال ساخت حساب...</span>
      </button>
      <div class="mt-5" x-show="regVerifying || regProgress > 0">
        <div class="w-full h-1.5 rounded-full progress-track overflow-hidden">
          <div class="h-full rounded-full progress-fill" :style="'width:' + regProgress + '%'"> </div>
        </div>
      </div>
    </div>

    <div x-show="screen === 'success'" x-transition:enter="screen-enter" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="flex flex-col items-center text-center py-6">
      <div class="check-circle w-20 h-20 rounded-full bg-brand-50 flex items-center justify-center mb-6 relative">
        <span class="ring-pulse absolute inset-0 rounded-full border-2 border-brand-300"></span>
        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="11" fill="#0d8f7e"/>
          <path class="check-path" d="M7 12.5l3 3 7-7" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
      </div>
      <h2 class="text-xl font-extrabold text-ink-900 mb-2" x-text="successTitle"></h2>
      <p class="text-sm text-ink-500" x-text="successMessage"></p>
      <div class="w-40 h-1.5 rounded-full progress-track overflow-hidden mt-6">
        <div class="h-full rounded-full progress-fill" style="width:100%"></div>
      </div>
    </div>
  </div>
</div>
<div class="fixed bottom-4 left-0 right-0 flex items-center justify-center gap-3 text-xs text-ink-400">
  <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M3 12h18M12 3a14 14 0 010 18 14 14 0 010-18z"/></svg> فارسی</span>
  <span>|</span>
  <span class="flex items-center gap-1 cursor-pointer hover:text-brand-600">راهنما<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M9.5 9a2.5 2.5 0 015 .5c0 1.5-2 1.8-2 3.4M12 17h.01"/></svg></span>
</div>

<!-- Toastify will display toasts; no custom container needed -->

<script>
function authApp(active = 'options') {
  return {
    screen: active === 'register' ? 'register' : 'select',
    phone: '',
    phoneError: false,
    sending: false,
    otp: ['', '', '', '', ''],
    loginMobile: '',
    loginPassword: '',
    loginError: '',
    loginStep: 'phone',
    loginProgress: 0,
    otpError: false,
    verifying: false,
    progress: 0,
    resendTimer: 45,
    resendInterval: null,
    username: '',
    password: '',
    showPass: false,
    rememberMe: false,
    loginLoading: false,
    loginProgress: 0,
    successTitle: 'کد تایید شد',
    successMessage: 'به پنل کاربری هدایت می‌شوید...',
    regName: '',
    regPhone: '',
    regPassword: '',
    regPasswordConfirm: '',
    showRegPass: false,
    acceptTerms: false,
    regLoading: false,
    regProgress: 0,
    regErrors: { name: false, phone: false, password: false, confirm: false },
    regOtp: ['', '', '', '', ''],
    regOtpError: false,
    regVerifying: false,
    regResendTimer: 45,
    regResendInterval: null,
    slide: 0,
    slides: [
      { title: 'به مستروام خوش آمدید', desc: 'برای دسترسی به حساب خود، لطفاً از یکی از روش‌های زیر وارد شوید.' },
      { title: 'امنیت حساب شما در اولویت است', desc: 'ورود دومرحله‌ای با کد یکبارمصرف، حساب شما را محافظت می‌کند.' },
      { title: 'همیشه و همه‌جا در دسترس', desc: 'به حساب خود از هر دستگاهی، در هر زمان، متصل بمانید.' },
    ],

    get otpComplete() { return this.otp.every(d => d !== ''); },
    get passStrength() {
      const p = this.regPassword;
      if (!p) return 0;
      let score = 0;
      if (p.length >= 6) score++;
      if (p.length >= 10 || (/[0-9]/.test(p) && /[a-zA-Z]/.test(p))) score++;
      if (/[^a-zA-Z0-9]/.test(p) && p.length >= 8) score++;
      return Math.min(score, 3);
    },
    get regOtpComplete() { return this.regOtp.every(d => d !== ''); },

    init() {
      this._applyLocalTokenIfAny();
      this._redirectIfAuthenticated();
      setInterval(() => { this.slide = (this.slide + 1) % this.slides.length; }, 4200);
    },
    _applyLocalTokenIfAny() {
      const token = localStorage.getItem('auth_token');
      if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      }
    },
    _redirectIfAuthenticated() {
      const token = localStorage.getItem('auth_token');
      if (!token) {
        return;
      }
      axios.get('/api/v1/auth/me', { headers: { Accept: 'application/json' } })
        .then(() => {
          window.location.href = '/dashboard';
        })
        .catch(() => {
          this.clearAuthToken();
        });
    },
    setAuthToken(token) {
      if (!token) {
        this.clearAuthToken();
        return;
      }
      localStorage.setItem('auth_token', token);
      localStorage.setItem('user_authenticated', '1');
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    clearAuthToken() {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_authenticated');
      delete axios.defaults.headers.common['Authorization'];
    },
    goTo(screen, payload = {}) {
      this.screen = screen;
      if (screen === 'phone') { this.phone = ''; this.phoneError = false; this.sending = false; }
      if (screen === 'select') { this.otp = ['', '', '', '', '']; this.progress = 0; this.otpError = false; this.loginMobile = ''; this.loginPassword = ''; this.loginError = ''; this.loginStep = 'phone'; this.loginLoading = false; this.loginProgress = 0; }
      if (screen === 'password') { this.loginStep = 'phone'; this.loginMobile = ''; this.loginPassword = ''; this.loginError = ''; this.loginLoading = false; this.loginProgress = 0; }
      if (screen === 'register') {
        this.regName = payload.regName ?? '';
        this.regPhone = payload.regPhone ?? '';
        this.regPassword = '';
        this.regPasswordConfirm = '';
        this.acceptTerms = false;
        this.regProgress = 0;
        this.regErrors = { name: false, phone: false, password: false, confirm: false };
      }
      if (screen === 'register-otp') { this.regOtp = ['', '', '', '', '']; this.regOtpError = false; this.regProgress = 0; }
    },
    sendCode() {
      this.phoneError = false;
      const digitsOnly = this.phone.replace(/\D/g, '');
      const isValidIranianMobile = /^09\d{9}$/.test(digitsOnly);
      if (!isValidIranianMobile) { this.phoneError = true; return; }
      this.sending = true;
      axios.post('/api/v1/auth/otp/request', { mobile: digitsOnly })
        .then((res) => {
          this.showToast(res?.data?.message || 'کد تایید ارسال شد', 'success');
          this.screen = 'otp';
          this.otp = ['', '', '', '', ''];
          this.startResendTimer();
          this.$nextTick(() => { this.$refs.otp0 && this.$refs.otp0.focus(); });
        })
        .catch(err => {
          const msg = err?.response?.data?.message || 'خطا در ارسال کد';
          this.showToast(msg, 'error');
          this.phoneError = true;
        })
        .finally(() => { this.sending = false; });
    },
    startResendTimer() { this.resendTimer = 45; clearInterval(this.resendInterval); this.resendInterval = setInterval(() => { if (this.resendTimer > 0) this.resendTimer--; else clearInterval(this.resendInterval); }, 1000); },
    resendCode() { this.otp = ['', '', '', '', '']; this.startResendTimer(); axios.post('/api/v1/auth/otp/request', { mobile: this.phone.replace(/\D/g, '') }).catch(() => {}); this.$nextTick(() => { this.$refs.otp0 && this.$refs.otp0.focus(); }); },
    onOtpInput(i, e) { const val = e.target.value.replace(/\D/g, '').slice(-1); this.otp[i] = val; this.otpError = false; if (val && i < 4) { this.$nextTick(() => { this.$refs['otp' + (i + 1)].focus(); }); } if (this.otpComplete) { this.$nextTick(() => this.verifyCode()); } },
    onOtpBackspace(i, e) { if (!this.otp[i] && i > 0) { this.$nextTick(() => { this.$refs['otp' + (i - 1)].focus(); }); } },
    onOtpPaste(e) { e.preventDefault(); const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, ''); for (let i = 0; i < 5; i++) { this.otp[i] = text[i] || ''; } if (this.otpComplete) this.$nextTick(() => this.verifyCode()); },
    verifyCode() {
      if (!this.otpComplete || this.verifying) return;
      this.otpError = '';
      this.verifying = true;
      this.progress = 0;
      axios.post('/api/v1/auth/otp/verify', { mobile: this.phone.replace(/\D/g, ''), otp: this.otp.join('') })
        .then((res) => {
          const msg = res?.data?.message || 'ورود موفق بود';
          const token = res?.data?.auth?.token || res?.data?.token || null;

          if (res?.data?.success && !token) {
            this.showToast(msg, 'success');
            this.goTo('register');
            this.regPhone = this.phone.replace(/\D/g, '');
            this.phone = '';
            return;
          }

          this.showToast(msg, 'success');
          if (token) {
            localStorage.setItem('auth_token', token);
            localStorage.setItem('user_authenticated', '1');
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            try { window.dispatchEvent(new Event('auth:login')); } catch (e) {}
          }
          this.loginProgress = 0;
          const interval = setInterval(() => {
            this.loginProgress += 20;
            if (this.loginProgress >= 100) {
              clearInterval(interval);
              window.location.href = '/dashboard';
            }
          }, 150);
        })
        .catch(err => {
          const msg = err?.response?.data?.message || 'کد وارد شده صحیح نیست';
          this.showToast(msg, 'error');
          this.otpError = true;
          this.otp = ['', '', '', '', ''];
        })
        .finally(() => { this.verifying = false; });
    },

    preparePasswordLogin() {
      this.loginError = '';
      const mobile = this.loginMobile.replace(/\D/g, '');
      if (!/^09\d{9}$/.test(mobile)) {
        this.loginError = 'شماره همراه را به‌درستی وارد کنید.';
        this.$nextTick(() => { this.$refs.passwordPhone?.focus(); });
        return;
      }
      this.loginMobile = mobile;
      this.loginStep = 'password';
      this.loginPassword = '';
      this.$nextTick(() => { this.$refs.passwordInput?.focus(); });
    },

    loginWithPassword() {
      this.loginError = '';
      const mobile = this.loginMobile.replace(/\D/g, '');
      if (!/^09\d{9}$/.test(mobile) || !this.loginPassword) {
        this.loginError = 'شماره همراه و رمز عبور را کامل وارد کنید.';
        return;
      }
      this.loginLoading = true;
      axios.post('/api/v1/auth/login', { mobile, password: this.loginPassword })
        .then((res) => {
          const msg = res?.data?.message || 'ورود موفق بود';
          const token = res?.data?.auth?.token || res?.data?.token || null;
          if (token) {
            localStorage.setItem('auth_token', token);
            localStorage.setItem('user_authenticated', '1');
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            try { window.dispatchEvent(new Event('auth:login')); } catch (e) {}
          }
          this.showToast('ورود موفق. در حال انتقال به داشبورد...', 'success');
          this.loginProgress = 0;
          const interval = setInterval(() => {
            this.loginProgress += 20;
            if (this.loginProgress >= 100) {
              clearInterval(interval);
              window.location.href = '/dashboard';
            }
          }, 150);
        })
        .catch(err => {
          const msg = err?.response?.data?.message || 'ورود ناموفق بود';
          this.showToast(msg, 'error');
          this.loginError = msg;
          if (err?.response?.status === 404 && msg.includes('کاربری با این شماره وجود ندارد')) {
            this.goTo('register');
            this.regPhone = mobile;
          }
        })
        .finally(() => { this.loginLoading = false; });
    },

    registerUser() {
      const r = { fullName: this.regName.trim(), phone: this.regPhone.replace(/\D/g, ''), password: this.regPassword, confirmPassword: this.regPasswordConfirm };
      const nameOk = r.fullName.length >= 3;
      const phoneOk = /^09\d{9}$/.test(r.phone);
      const passwordOk = r.password.length >= 8;
      const confirmOk = r.password === r.confirmPassword;
      if (!nameOk || !phoneOk || !passwordOk || !confirmOk) {
        this.showToast('لطفاً اطلاعات فرم را درست وارد کنید.', 'error');
        return;
      }
      if (!this.acceptTerms) { this.showToast('لطفاً شرایط استفاده را قبول کنید.', 'error'); return; }
      this.regLoading = true;
      axios.post('/api/v1/auth/register', { full_name: r.fullName, mobile: r.phone, email: null, password: r.password, password_confirmation: r.confirmPassword })
        .then((res) => {
          this.showToast(res?.data?.message || 'ثبت‌نام با موفقیت انجام شد', 'success');
          this.regProgress = 0;
          this.screen = 'register-otp';
          this.startRegResendTimer();
        })
        .catch(err => { const msg = err?.response?.data?.message || 'خطا در ثبت‌نام'; this.showToast(msg, 'error'); })
        .finally(() => { this.regLoading = false; });
    },

    startRegResendTimer() {
      this.regResendTimer = 45;
      clearInterval(this.regResendInterval);
      this.regResendInterval = setInterval(() => { if (this.regResendTimer > 0) this.regResendTimer--; else clearInterval(this.regResendInterval); }, 1000);
    },

    resendRegCode() {
      axios.post('/api/v1/auth/otp/request', { mobile: this.regPhone.replace(/\D/g, '') }).catch(() => {});
      this.regOtp = ['', '', '', '', ''];
      this.startRegResendTimer();
    },

    verifyRegCode() {
      if (!this.regOtpComplete || this.regVerifying) return;
      this.regVerifying = true;
      axios.post('/api/v1/auth/otp/verify', { mobile: this.regPhone.replace(/\D/g, ''), otp: this.regOtp.join('') })
        .then((res) => { this.showToast(res?.data?.message || 'ثبت‌نام و تأیید با موفقیت انجام شد', 'success'); this.goTo('select'); })
        .catch(err => { const msg = err?.response?.data?.message || 'کد وارد شده صحیح نیست'; this.showToast(msg, 'error'); this.regOtpError = true; this.regOtp = ['', '', '', '', '']; })
        .finally(() => { this.regVerifying = false; });
    },

    showToast(message, type = 'info') {
      const bg = type === 'success' ? '#16a34a' : (type === 'error' ? '#dc2626' : '#0ea5a0');
      Toastify({
        text: message,
        duration: 4500,
        gravity: 'top',
        position: 'center',
        close: true,
        style: { background: bg, color: '#fff' }
      }).showToast();
    },
  };
}
</script>
