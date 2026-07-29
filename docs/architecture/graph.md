# SHVAM Knowledge Graph

این سند یک نمای کلی از معماری فعلی پروژه SHVAM است. مخزن در حال حاضر بر روی لایه‌ی بک‌اند و APIهای Laravel متمرکز است و هیچ فرانت‌اند مستقل تحت مدیریت این مخزن در حال نگهداری نیست.

## ۱. نمای کلی پروژه

- پروژه بر پایه‌ی Laravel 12 ساخته شده است.
- معماری پروژه از نوع ماژولار مونولیت است.
- کد در دو فضای نام اصلی قرار دارد:
  - App\ برای لایه‌ی اپلیکیشن و سرویس‌های پایه
  - Modules\ برای ماژول‌های دامنه‌ای و سرویس‌های اختصاصی
- بخش vendor/ وابستگی‌های Composer را نگه می‌دارد.
- مسیر اصلی بارگذاری ماژول‌ها در app/Providers/AppServiceProvider.php قرار دارد.

## ۲. نقطه شروع بارگذاری

- فایل اصلی بارگذاری ماژول‌ها: app/Providers/AppServiceProvider.php
- این ServiceProvider ماژول‌های پایه و سرویس‌های اصلی را ثبت می‌کند:
  - Modules\Shared\Providers\SharedServiceProvider
  - App\Providers\RepositoryServiceProvider
  - App\Providers\UserManagementServiceProvider
  - App\Providers\AuthenticationServiceProvider
  - Modules\Workflow\Providers\WorkflowServiceProvider
  - Modules\Documents\Providers\DocumentsServiceProvider
  - Modules\KYC\Providers\KycServiceProvider
  - Modules\Ledger\Providers\LedgerServiceProvider
  - Modules\Wallet\Providers\WalletServiceProvider
  - Modules\Advertisements\Providers\AdvertisementsServiceProvider

## ۳. ساختار کلی ماژول‌ها

هر ماژول معمولاً شامل این بخش‌ها است:

- Config/ : تنظیمات ماژول
- Database/Migrations/ : مایگریشن‌های جدول‌ها
- Models/ : مدل‌های Eloquent
- Repositories/ : مخازن داده
- Services/ : منطق تجاری
- Actions/ : اعمال مشخص و قابل ترکیب
- DTO/ : اشیاء انتقال داده
- Events/ و Listeners/ : سیستم رویداد
- Policies/ : سیاست‌های دسترسی
- Http/Controllers/ : کنترلرهای API
- Http/Requests/ : اعتبارسنجی درخواست‌ها
- Routes/api.php : مسیرهای اکسپوز شده

## ۴. ماژول‌های اصلی و نقش‌شان

### ۴.۱ Shared

- نوع: پایه‌ای
- مسئولیت: کلاس‌های بنیادی، کانفیگ مشترک و ابزارهایی که همه‌ی ماژول‌ها از آن استفاده می‌کنند.
- provider: Modules\Shared\Providers\SharedServiceProvider

### ۴.۲ Authentication

- نوع: زیرساختی
- provider wrapper: App\Providers\AuthenticationServiceProvider
- provider ماژول: Modules\Authentication\Providers\AuthenticationServiceProvider
- مسئولیت: احراز هویت، OTP، نشست و سرویس‌های مرتبط با ورود.

### ۴.۳ UserManagement

- نوع: دامنه‌ای
- provider: Modules\UserManagement\Providers\UserManagementServiceProvider
- مسئولیت: مدیریت کاربران، نقش‌ها، مجوزها و داده‌های پروفایل.

### ۴.۴ KYC

- نوع: دامنه‌ای
- provider: Modules\KYC\Providers\KycServiceProvider
- مسئولیت: جریان درخواست KYC، وضعیت تایید و دسترسی‌های مرتبط.

### ۴.۵ Workflow

- نوع: دامنه‌ای
- provider: Modules\Workflow\Providers\WorkflowServiceProvider
- مسئولیت: موتور گردش کار، تعریف وضعیت‌ها، تاییدیه‌ها و اجرای جریان‌ها.

### ۴.۶ Ledger

- نوع: دامنه‌ای / هسته حسابداری
- provider: Modules\Ledger\Providers\LedgerServiceProvider
- مسئولیت: ثبت تراکنش‌های مالی دوتایی، مانده‌ها، ژورنال و اسنپ‌شات حسابداری.

### ۴.۷ Wallet

- نوع: کاربردی / سرویس مالی
- provider: Modules\Wallet\Providers\WalletServiceProvider
- مسئولیت: مدیریت کیف پول، مانده، انتقال، قفل‌سازی و محدودیت‌ها.

### ۴.۸ Advertisements

- نوع: دامنه‌ای
- provider: Modules\Advertisements\Providers\AdvertisementsServiceProvider
- مسئولیت: آگهی‌ها، جریان کاری آگهی، و تعامل با KYC/Workflow.

## ۵. جریان اجرای درخواست

1. درخواست HTTP به Routes/api.php مربوطه می‌رسد.
2. کنترلر درخواست را دریافت می‌کند.
3. کنترلر از Service یا Action استفاده می‌کند.
4. Service از Repositoryها برای خواندن و نوشتن داده استفاده می‌کند.
5. داده‌ها در Models/Eloquent ذخیره می‌شوند.
6. در صورت نیاز Event و Listener یا Notification اجرا می‌شود.

## ۶. نکات معماری مهم

- Ledger منبع حقیقت نهایی برای تراکنش‌های مالی است.
- Wallet باید از Ledger برای ثبت تغییرات مالی استفاده کند و مستقیم مانده را دستکاری نکند.
- هر ماژول باید از ServiceProvider خود برای ثبت تنظیمات، مسیرها و وابستگی‌ها استفاده کند.
- منطق تجاری در Services و Actions نگهداری می‌شود و دسترسی‌ها در Policies تعریف می‌شوند.

## ۹. ساختار مسیرها و مایگریشن‌ها

- مسیرهای ماژول‌ها عمدتاً در `Modules/<Module>/Routes/api.php` قرار دارند.
- مایگریشن‌های هر ماژول در `Modules/<Module>/Database/Migrations/` قرار دارد.
- مایگریشن‌های اصلی قابل شناسایی:
  - `Modules/Ledger/Database/Migrations/2026_07_07_000001_create_ledger_engine_tables.php`
  - `Modules/Wallet/Database/Migrations/2026_07_08_000001_create_wallet_module_tables.php`
  - `Modules/KYC/Database/Migrations/2026_07_07_000001_create_kyc_module_tables.php`
  - `Modules/Documents/Database/Migrations/2026_07_07_000001_create_documents_module_tables.php`
  - `Modules/Workflow/Database/Migrations/2026_07_07_000001_create_workflow_engine_support_tables.php`

## ۱۰. نکات مهم برای توسعه‌دهنده جدید

- اولین فایل برای بررسی: `app/Providers/AppServiceProvider.php`
- اگر می‌خواهی ویژگی مالی اضافه کنی، ابتدا `Ledger` را بررسی کن.
- `Wallet` فقط باید از Ledger بخواند و به Ledger بنویسد.
- `Shared` کلاس‌های بنیادی و config مشترک را نگهداری می‌کند.
- مسیرهای API و سرویس‌های ماژول‌ها ساختار استانداردی دارند.

## ۱۱. پیشنهاد برای گسترش آتی

- تکمیل و استانداردسازی `Wallet` برای عملکردهای deposit/withdraw/transfer
- نوشتن تست‌های اتصالی برای پوشش خطی Ledger و Wallet
- اضافه کردن `GraphQL` یا `OpenAPI` برای مستندسازی API
- پیاده‌سازی `event sourcing` یا `audit trail` دقیق‌تر برای تراکنش‌های مالی
- ساخت یک مستند معماری تصویری با `graphviz` یا نمودار سازمانی برای تیم

## ۱۲. نتیجه

این پروژه یک پلتفرم مالی-بازارگاهی با معماری ماژولار است که:
- حالت حسابداری امن و غیرقابل تغییر دارد
- کیف پول کاربر را با اتصال به دفتر کل پشتیبانی می‌کند
- هویتیابی، KYC، مدارک و گردش کار را همزمان مدیریت می‌کند
- توسعه‌پذیر است و می‌توان ماژول‌های جدید را براساس الگوی موجود اضافه کرد

---

> فایل `docs/graph.md` اکنون آماده است و شرح کامل معماری و امکانات کل پروژه را دارد.
