
# Advertisements Module

این ماژول بخشی از معماری ماژولارِ پلتفرم مالی-بازارگاهی است و مسئول مدیریت تبلیغات، علاقه‌مندی کاربران و تولید پیشنهادهای مرتبط (recommendations) می‌باشد. این ماژول فقط برای چرخه‌ی ثبت، ویرایش، انتشار، بازدید و تعامل با آگهی‌ها طراحی شده است و مسئولیت مدیریت بانک‌ها، طرح‌های بانکی یا کاتالوگ محصولات اعتباری در Banks قرار دارد.

## معرفی کوتاه (فارسی)

ماژول `Advertisements` عملیات زیر را فراهم می‌کند: ایجاد و ویرایش تبلیغ، ارسال برای بررسی (گردش‌کار)، نمایش جزئیات و ثبت بازدید، علاقه‌مندی (favorite/unfavorite)، نمایش لیست علاقه‌مندی‌های کاربر، و محاسبهٔ توصیه‌ها با وزن‌های قابل تنظیم.

لاجیک تجاری در `Services/` و `Actions/` قرار دارد، دسترسی به داده در `Repositories/` انتزاع شده، و ارتباطات غیرهمزمان از طریق `Jobs/` و `Listeners/` پیاده‌سازی می‌شود.

## امکانات اصلی

- CRUD تبلیغات (شامل تصاویر و اسناد مرتبط)
- ارسال تبلیغ برای بررسی و گردش‌کار (submit → approve/reject/archive/resume)
- علاقه‌مندی (favorite/unfavorite) و لیست علاقه‌مندی‌های کاربر
- ضبط بازدیدها با میدلور `record_ad_view`
- الگوریتم توصیه (Recommendation) با وزن‌های قابل تنظیم در `Config/recommendation.php`
- ایندکس و پردازش آسنکرون با Jobs و Listeners
- پشتیبانی از انتخاب موقعیت آگهی با استان و شهر، شامل APIهای جداگانه برای دریافت استان‌ها و شهرهای هر استان و اعتبارسنجی اینکه `city_id` متعلق به `province_id` انتخاب‌شده باشد

## جریان انتخاب موقعیت برای ایجاد آگهی

در فرآیند ایجاد آگهی، کاربر ابتدا استان و سپس شهر مربوطه را از فرم انتخاب می‌کند. برای این کار دو endpoint مجزا در سطح Shared آماده شده‌اند:

- `GET /api/v1/locations/provinces` — دریافت فهرست استان‌ها
- `GET /api/v1/locations/provinces/{province_id}/cities` — دریافت شهرهای یک استان مشخص

منطق بازیابی این داده‌ها در سرویس مشترک `Modules/Shared/Services/LocationService` متمرکز شده است تا هم برای صفحه آگهی و هم برای سایر ماژول‌ها قابل استفاده باشد. در مرحلهٔ ایجاد آگهی، لایهٔ Request و Service مربوط به Advertisements بررسی می‌کنند که اگر کاربر شهر را با استان نامتناسب انتخاب کند، درخواست با خطای اعتبارسنجی رد شود. این رویکرد باعث می‌شود داده‌های ورودی در همهٔ مسیرهای ایجاد آگهی یکنواخت و قابل اعتماد باشند.

## ساختار پوشه‌ها و نقش هر کدام

- `Actions/` : کلاس‌های عملیاتی یک‌وظیفه‌ای (مثلاً `CreateAdvertisementAction`).
- `Config/` : تنظیمات ماژول مانند `recommendation.php`.
- `Http/Controllers/` : کنترلرهای API مانند `DiscoveryController` و `AdvertisementController`.
- `Database/` : مایگریشن‌ها، factories و seeders مخصوص ماژول.
- `DTO/` : اشیاء انتقال داده برای رد/پذیرش ورودی‌ها و خروجی‌ها.
- `Enums/` : enum های وضعیت و دیدپذیری (`AdvertisementStatus`, `AdvertisementVisibility`).
- `Events/` و `Listeners/` : رویدادهای دامنه‌ای و واکنش‌ها (مثلاً invalidate cache، ارسال نوتیفیکیشن).
- `Jobs/` : کارهای آسنکرون (مثلاً `IndexAdvertisementJob`, `PrecomputeRecommendationsJob`).
- `Models/` : مدل‌های Eloquent مانند `Advertisement`, `AdvertisementImage`, `AdvertisementDocument`.
- `Repositories/` : اینترفیس‌ها و پیاده‌سازی Eloquent برای دسترسی به DB (مانند `AdvertisementFavoriteRepository`).
- `Resources/` : API Resources برای شکل‌دهی پاسخ‌ها.
- `Routes/` : فایل `Routes/api.php` که endpointها و میدلورها را ثبت می‌کند.
- `Services/` : سرویس‌های دامنه‌ای مانند `FavoriteService` و `AdvertisementRecommendationService`.
- `Tests/` : تست‌های Unit و Feature ماژول.

## فایل‌های کلیدی و نقش آنها

- `Routes/api.php` — ثبت مسیرهای HTTP تحت prefix `/api/advertisements` و استفاده از میدلورهایی مثل `auth:sanctum` و `record_ad_view`.
- `Http/Controllers/DiscoveryController.php` — لیست‌ها، نمایش جزئیات، favorite/unfavorite، recommended endpoints.
- `Http/Controllers/AdvertisementController.php` — ایجاد و ارسال تبلیغ برای بررسی.
- `Services/FavoriteService.php` — منطق favorite/unfavorite و انتشار رویداد `AdvertisementFavorited`.
- `Repositories/Eloquent/AdvertisementFavoriteRepository.php` — عملیات insert/delete/list روی جدول `advertisement_favorites`.
- `Providers/AdvertisementsServiceProvider.php` — ثبت بایندینگ‌ها (bind/singleton) و اتصال لیسنرها به events.
- `Config/recommendation.php` — وزن‌ها و تنظیمات الگوریتم توصیه.

## وابستگی‌ها و پیش‌نیازها

- وابستگی‌های PHP (در سطح پروژه): `php ^8.2`, `laravel/framework ^12`, `laravel/sanctum`.
- پکیج‌های مفید: `spatie/laravel-permission` (برای نقش‌ها و مجوزها).
- ماژول‌های داخلی مورد نیاز: `Modules/Shared`, `Modules/UserManagement`, `Modules/Documents`, `Modules/Workflow`, `Modules/Notifications`.
- سرویس‌های runtime: MySQL/MariaDB (utf8mb4)، Redis (برای cache و queue پیشنهاد می‌شود)، فضای ذخیره‌سازی S3-compatible برای رسانه‌ها (اختیاری).

## نصب محلی و راه‌اندازی سریع

1. فایل `.env` را از `.env.example` بساز و تنظیمات DB و queue را تکمیل کن.

2. وابستگی‌ها را نصب کن:

```bash
composer install
npm install    # در صورت استفاده از assets فرانت‌اند
```

3. کلید اپ را بساز و مایگریشن‌ها را اجرا کن:

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed   # در صورت وجود seeders مرتبط
```

4. (اختیاری) برای پردازش توصیه‌ها یا ایندکس‌ها jobها را اجرا یا dispatch کن:

```bash
php artisan queue:work
php artisan tinker --execute="\\Modules\\Advertisements\\Jobs\\PrecomputeRecommendationsJob::dispatchNow()"
```

## اجرای تست‌ها

```bash
php artisan test Modules/Advertisements/Tests
```

نکات تست: برای مسیرهای محافظت‌شده از `actingAs($user, 'sanctum')` استفاده کن؛ برای fake کردن events یا notifications از `Event::fake()` / `Notification::fake()` بهره ببر.

## API endpoints مهم

- `GET /api/advertisements` — فهرست تبلیغات (فیلتر/صفحه‌بندی)
- `GET /api/advertisements/{uuid}` — جزئیات تبلیغ (میلدور: ثبت بازدید)

Response fields (selected):

- `priority` (int): عدد اولویت آگهی. مقدار بالاتر نشان‌دهنده اولویت بالاتر است.
- `priority_label` (string): برچسب انسان‌خوان معادل اولویت (مثلاً `VIP`, `فوری`, `اورژانسی`, `معمولی`). این مقدار در `AdvertisementListResource` اضافه شده تا فرانت‌اند به‌راحتی نمایش دهد.

- `POST /api/advertisements` — ایجاد تبلیغ (نیازمند `auth:sanctum`)
- `POST /api/advertisements/{id}/submit` — ارسال تبلیغ برای بررسی (auth)
- `POST /api/advertisements/{uuid}/favorite` — افزودن به علاقه‌مندی‌ها (auth)
- `DELETE /api/advertisements/{uuid}/favorite` — حذف از علاقه‌مندی‌ها (auth)
- `GET /api/advertisements/user/favorites` — لیست علاقه‌مندی‌های کاربر (auth)

## نکات عملی و پیشنهادها

- از Redis برای cache کردن نتایج توصیه و اجرای jobهای پیش‌محاسبه استفاده کن تا بار سرور کاهش یابد.
- اطمینان از اجرای مایگریشن `advertisement_favorites` تا repository مربوطه بدون خطا کار کند.
- مستندسازی API با OpenAPI/Swagger به تیم توسعه و مصرف‌کنندگان API کمک می‌کند.

---

فایل JSON خلاصهٔ ماژول در `Modules/Advertisements/README.json` نیز ایجاد شده است.

اگر مایل باشی، در مرحلهٔ بعد می‌توانم نمونه payloadهای API (curl/HTTPie)، یا مایگریشن `advertisement_favorites` و یک seeder نمونه بسازم.

## نگهداری و اسکریپت‌های کمکی

برای سهولت کار در محیط توسعه اسکریپت‌های ساده‌ای در پوشه `scripts/` اضافه شده‌اند:

- `scripts/ads_columns.php`: لیست ستون‌های جدول `advertisements` را چاپ می‌کند — مفید برای بررسی اینکه ستون‌هایی مثل `priority` در دیتابیس وجود دارند یا نه.
- `scripts/mark_last10.php`: اسکریپتی که رکوردهای آخر جدول `advertisements` را انتخاب کرده و مقدار `priority` آن‌ها را به `3` (برچسب: اورژانسی) تنظیم می‌کند. این اسکریپت فقط زمانی کار خواهد کرد که ستون `priority` در جدول وجود داشته باشد.

نمونه اجرا (از ریشه پروژه):

```bash
php scripts/ads_columns.php
php scripts/mark_last10.php
```

نکات مهم:
- اگر هنگام اجرای `mark_last10.php` با خطای "Unknown column" یا مشابه مواجه شدی، ابتدا باید مایگریشن مربوط به ستون `priority` را اجرا کنی. مایگریشن‌ها در `database/migrations/` قرار دارند و می‌توانی با دستور زیر مایگریت کنی:

```bash
php artisan migrate
```

- اگر نمی‌خواهی مایگریشن در محیط تولید اجرا شود، از یک branch محلی و یا محیط توسعه‌ای استفاده کن و قبل از اعمال تغییرات روی دیتابیسِ تولید، از تیم DBA یا مالک دیتابیس تایید بگیر.

اگر می‌خواهی من این مایگریشن را روی محیط توسعه اجرا کنم یا برایت یک seeder بنویسم تا چند آگهی تستی با `priority` مقداردهی شود، بگو تا انجام دهم.

