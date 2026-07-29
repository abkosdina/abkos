# راهنمای نصب و راه‌اندازی سیستم بازدید، تماس و تاریخ جلالی

## 📦 نیازمندی‌ها

- Laravel 12.0+
- PHP 8.2+
- Database (MySQL, PostgreSQL, etc.)

## 🔧 مراحل نصب

### 1️⃣ مهاجرت دیتابیس

```bash
# اجرای تمام مهاجرات
php artisan migrate

# یا فقط مهاجرات جدید
php artisan migrate --path=database/migrations
```

**جداول ایجادشده:**
- `advertisement_contacts` - تماس‌های تبلیغات
- ستون‌های جدید در `advertisements` (`views_count`, `contacts_count`)

### 2️⃣ Register Middleware

برای استفاده از ثبت بازدید خودکار، middleware را ثبت کنید:

```php
// app/Http/Middleware/HttpKernel.php
protected $middlewareAliases = [
    // ... سایر middleware‌ها ...
    'record.advertisement.view' => \Modules\Advertisements\Http\Middleware\RecordAdvertisementView::class,
];
```

### 3️⃣ Routes

تنظیم routes برای بازدید و تماس:

```php
// routes/api.php

// بازدید (با middleware خودکار)
Route::middleware('record.advertisement.view')->group(function () {
    Route::get('/ads/{uuid}', [AdvertisementController::class, 'show']);
    Route::get('/advertisements/{uuid}', [AdvertisementController::class, 'show']);
});

// تماس
Route::post('/advertisements/{id}/contacts', [ContactController::class, 'store']);
Route::post('/ads/{id}/contacts', [ContactController::class, 'store']);
```

### 4️⃣ Models

اضافه کردن HasJalaliDates Trait به Models:

```php
<?php

namespace Modules\Advertisements\Models;

use App\Traits\HasJalaliDates;

class Advertisement extends Model
{
    use HasJalaliDates;
    
    /**
     * تاریخ‌هایی که باید تبدیل شوند
     */
    protected $jalaliDates = [
        'published_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];
}
```

## ✨ استفاده

### سیستم بازدید

```php
// خودکار از طریق Middleware
GET /ads/{uuid}

// جدول advertisement_views:
// - ثبت شدن بازدید
// - Deduplication (هر 60 دقیقه)
// - views_count افزایش می‌یابد
```

### سیستم تماس

```php
// ثبت تماس جدید
POST /api/advertisements/{id}/contacts
Content-Type: application/json

{
    "name": "علی احمدی",
    "email": "ali@example.com",
    "phone": "09123456789",
    "message": "این کالا را برای من رزرو کنید"
}

// Response:
{
    "message": "Contact inquiry submitted successfully",
    "data": {
        "advertisement_id": 1,
        "status": "pending"
    }
}
```

### سیستم تاریخ جلالی

```php
// دریافت تاریخ جلالی
$ad = Advertisement::find(1);
echo $ad->published_at;  // 1405-04-24

// دریافت فرمت‌شده
echo $ad->getPublishedAtJalaliFormatted();  // 24 مهر 1405 14:30

// تنظیم تاریخ جلالی
$ad->published_at = '1405-04-24';
$ad->save();  // خودکار تبدیل به میلادی می‌شود

// دریافت تماس‌ها
$contact = AdvertisementContact::find(1);
echo $contact->created_at;  // 1405-04-24
echo $contact->getCreatedAtJalaliFormatted();  // 24 مهر 1405 14:30
```

## 📊 API Endpoints

### بازدید
| Method | Path | توضیح |
|--------|------|-------|
| GET | `/ads/{uuid}` | نمایش تبلیغ + ثبت بازدید |

### تماس
| Method | Path | توضیح |
|--------|------|-------|
| POST | `/api/advertisements/{id}/contacts` | ثبت تماس جدید |
| GET | `/api/advertisements/{id}/contacts` | دریافت تماس‌ها |
| GET | `/api/contacts/{id}` | دریافت جزئیات تماس |
| PUT | `/api/contacts/{id}` | به‌روز‌رسانی وضعیت تماس |

## 🔍 Validation

### درخواست تماس

```
name        : required, string, max:255
email       : required, email, max:255
phone       : nullable, string, max:20
message     : required, string, min:10, max:5000
```

## 📚 مستندات

برای مطالعه مفصل، به فایل‌های مستندات مراجعه کنید:

- **[JALALI_DATE_SYSTEM.md](./JALALI_DATE_SYSTEM.md)** - سیستم تاریخ جلالی
- **[VIEWS_AND_CONTACTS_SYSTEM.md](./VIEWS_AND_CONTACTS_SYSTEM.md)** - سیستم بازدید و تماس
- **[IMPLEMENTATION_SUMMARY.md](./IMPLEMENTATION_SUMMARY.md)** - خلاصه پیاده‌سازی

## 🐛 Troubleshooting

### مسئله: بازدید ثبت نمی‌شود

**حل:**
1. middleware در routes ثبت شده است؟
2. جدول `advertisement_views` وجود دارد؟
3. UUID صحیح است؟

```bash
# بررسی جداول
php artisan migrate:status

# بررسی routes
php artisan route:list | grep ads
```

### مسئله: تاریخ میلادی نمایش می‌دهد

**حل:**
- `jalaliDates` array در model تعریف شده است؟

```php
// ✅ صحیح
protected $jalaliDates = ['published_at', 'created_at'];

// ❌ غلط
// خالی یا تعریف نشده
```

### مسئله: Validation errors

**حل:**
- تمام فیلدهای لازم ارسال شده‌اند؟
- داده‌های صحیح ارسال شده‌اند؟

```bash
# Log فایل بررسی کنید
tail -f storage/logs/laravel.log
```

## 🚀 بهینه‌سازی

### Cache
```php
// views_count را cache کنید
Cache::remember("ad:views:{$adId}", 60, fn() => $ad->views_count);
```

### Deduplication Duration
```bash
# .env
AD_VIEW_DEDUPE_MINUTES=60  # یا هر عدد دیگری
```

### Database Indexing
```sql
-- نمایه‌های موجود در migrations
CREATE INDEX idx_advertisement_views_ad_id ON advertisement_views(advertisement_id);
CREATE INDEX idx_contacts_ad_id ON advertisement_contacts(advertisement_id);
CREATE INDEX idx_contacts_status ON advertisement_contacts(status);
CREATE INDEX idx_contacts_created_at ON advertisement_contacts(created_at);
```

## 📈 نمونه Query‌ها

### کل بازدید‌های یک تبلیغ
```php
$ad = Advertisement::find(1);
$totalViews = $ad->views_count;
```

### بازدید‌های امروز
```php
$todayViews = Advertisement::find(1)
    ->views()
    ->whereDate('created_at', today())
    ->count();
```

### تماس‌های معلق
```php
$pendingContacts = Advertisement::find(1)
    ->contacts()
    ->pending()
    ->get();
```

### تماس‌های یک ایمیل خاص
```php
$contacts = AdvertisementContact::where('email', 'user@example.com')
    ->get();
```

### تماس‌های در بازه زمانی
```php
use App\Traits\HasJalaliDates;

$start = HasJalaliDates::jalaliToGregorian('1405-01-01');
$end = HasJalaliDates::jalaliToGregorian('1405-12-29');

$contacts = AdvertisementContact::whereBetween('created_at', [$start, $end])
    ->get();
```

## 🔐 نکات امنیتی

- ✅ Validation تمام ورودی‌ها (StoreAdvertisementContactRequest)
- ✅ SQL Injection protection (Eloquent ORM)
- ✅ Rate limiting via Deduplication
- ✅ null coalescing برای user_id
- ✅ Exception handling در services

## 🎯 بیشتر بخوانید

### فایل‌های مهم
```
app/Traits/HasJalaliDates.php
Modules/Advertisements/Models/AdvertisementContact.php
Modules/Advertisements/Services/ViewService.php
Modules/Advertisements/Services/ContactService.php
Modules/Advertisements/Http/Controllers/ContactController.php
database/migrations/2026_07_15_*.php
```

### Environment Variables
```bash
# .env
AD_VIEW_DEDUPE_MINUTES=60
CACHE_DRIVER=redis  # بهتر از file برای deduplication
```

## ✅ Checklist

- [ ] مهاجرات اجرا شده‌اند
- [ ] Middleware ثبت شده است
- [ ] Routes تنظیم شده‌اند
- [ ] Models Trait دارند
- [ ] jalaliDates تعریف شده‌اند
- [ ] Tests پاس شده‌اند

## 📞 پشتیبانی و بهبود

برای گزارش خطا یا پیشنهادات:
1. Issue در GitHub
2. Pull Request
3. Email

---

**نسخه**: 1.0  
**آخرین به‌روز‌رسانی**: 1405-04-24  
**وضعیت**: ✅ Production Ready
