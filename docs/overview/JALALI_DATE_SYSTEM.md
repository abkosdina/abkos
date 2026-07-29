# سیستم تاریخ جلالی (Jalali Date Conversion System)

## مقدمه

این سند شرح می‌دهد که چگونه سیستم تاریخ جلالی در پروژه پیاده‌سازی شده است. تمام تاریخ‌ها در دیتابیس به صورت میلادی (Gregorian) در فرمت ISO 8601 ذخیره می‌شوند، اما هنگام نمایش به کاربران ایرانی، به صورت شمسی (جلالی) نمایش داده می‌شوند.

## معماری

### 1. ذخیره‌سازی در دیتابیس
- **فرمت**: ISO 8601 Gregorian (میلادی)
- **مثال**: `2026-07-15 14:30:00`
- **دلیل**: سازگاری بین‌المللی و استانداردهای بانک اطلاعات

### 2. نمایش به کاربر
- **فرمت**: شمسی/جلالی (Jalali)
- **مثال**: `1405-04-24`
- **نمایش فاشن**: `24 مهر 1405` (روز ماه سال)

### 3. دریافت از کاربر
- کاربری می‌تواند تاریخ را به صورت جلالی وارد کند
- سیستم خودکار آن را به میلادی تبدیل می‌کند
- سپس در دیتابیس ذخیره می‌کند

## Trait: `HasJalaliDates`

### موقعیت فایل
```
app/Traits/HasJalaliDates.php
```

### نحوه استفاده

#### 1. اضافه کردن Trait به Model

```php
<?php

namespace Modules\Advertisements\Models;

use App\Traits\HasJalaliDates;
use Illuminate\Database\Eloquent\Model;

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

#### 2. تعریف `jalaliDates` Array

```php
protected $jalaliDates = [
    'published_at',    // تاریخ انتشار
    'expires_at',      // تاریخ انقضاء
    'created_at',      // تاریخ ایجاد
    'updated_at',      // تاریخ آخرین تغییر
];
```

### روش‌های استفاده

#### روش 1: دریافت خودکار تاریخ جلالی
```php
$ad = Advertisement::find(1);

// دریافت به صورت جلالی (خودکار)
echo $ad->published_at;  // خروجی: 1405-04-24
echo $ad->expires_at;    // خروجی: 1405-10-15
```

#### روش 2: دریافت جلالی با فرمت فاشن (نام ماه)
```php
$ad = Advertisement::find(1);

// دریافت با نام ماه فارسی
echo $ad->getPublishedAtJalaliFormatted();
// خروجی: 24 مهر 1405 14:30

echo $ad->getPublishedAtJalaliFormatted('d M Y');
// خروجی: 24 مهر 1405
```

#### روش 3: تنظیم تاریخ از ورودی جلالی
```php
$ad = new Advertisement();

// تنظیم تاریخ به صورت جلالی
$ad->published_at = '1405-04-24';  // خودکار به میلادی تبدیل می‌شود
$ad->save();

// در دیتابیس ذخیره می‌شود: 2026-07-15
```

#### روش 4: تبدیل دستی

```php
use App\Traits\HasJalaliDates;

// میلادی به جلالی
$jalali = HasJalaliDates::gregorianToJalali('2026-07-15');
// خروجی: 1405-04-24

// جلالی به میلادی
$gregorian = HasJalaliDates::jalaliToGregorian('1405-04-24');
// خروجی: Carbon object (2026-07-15)

// فرمت‌بندی جلالی
$formatted = HasJalaliDates::formatJalaliDate('1405-04-24', 'd M Y');
// خروجی: 24 مهر 1405
```

### ماه‌های جلالی

```php
1 = فروردین     7 = مهر
2 = اردیبهشت    8 = آبان
3 = خرداد      9 = آذر
4 = تیر        10 = دی
5 = مرداد      11 = بهمن
6 = شهریور     12 = اسفند
```

## مثال‌های عملی

### مثال 1: ایجاد تبلیغ با تاریخ جلالی

```php
use Modules\Advertisements\Models\Advertisement;

$ad = Advertisement::create([
    'title' => 'یک تبلیغ نمونه',
    'description' => 'توضیحات',
    'published_at' => '1405-04-24',  // جلالی - خودکار تبدیل می‌شود
    'expires_at' => '1405-10-15',    // جلالی - خودکار تبدیل می‌شود
]);

// در دیتابیس ذخیره می‌شود:
// published_at: 2026-07-15 00:00:00
// expires_at: 2026-12-05 00:00:00
```

### مثال 2: نمایش تاریخ‌ها در جواب API

```php
$ad = Advertisement::find(1);

return response()->json([
    'id' => $ad->id,
    'title' => $ad->title,
    'published_at_jalali' => $ad->getPublishedAtJalaliFormatted(),
    'expires_at_jalali' => $ad->getExpiresAtJalaliFormatted(),
    // خروجی:
    // "published_at_jalali": "24 مهر 1405 14:30"
    // "expires_at_jalali": "15 دی 1405 00:00"
]);
```

### مثال 3: جستجو براساس تاریخ

```php
// تبدیل جلالی به میلادی قبل از جستجو
$jalali = '1405-04-24';
$gregorian = HasJalaliDates::jalaliToGregorian($jalali);

$ads = Advertisement::whereDate('published_at', '>=', $gregorian)
    ->get();
```

### مثال 4: محدوده تاریخ

```php
$startJalali = '1405-01-01';
$endJalali = '1405-12-29';

$startGregorian = HasJalaliDates::jalaliToGregorian($startJalali);
$endGregorian = HasJalaliDates::jalaliToGregorian($endJalali);

$ads = Advertisement::whereBetween('published_at', [$startGregorian, $endGregorian])
    ->get();
```

## سیستم شمارش بازدید و تماس

### ساختار جداول

#### جدول: `advertisement_views`
```
id              - شناسه
user_id         - شناسه کاربر (nullable)
advertisement_id - شناسه تبلیغ
ip              - آدرس IP
device          - User-Agent
session_id      - شناسه نشست
created_at      - تاریخ (میلادی - خودکار تبدیل به جلالی)
updated_at      - تاریخ (میلادی - خودکار تبدیل به جلالی)
```

#### جدول: `advertisement_contacts`
```
id              - شناسه
advertisement_id - شناسه تبلیغ
user_id         - شناسه کاربر (nullable)
name            - نام تماس‌گیرنده
email           - ایمیل
phone           - تلفن (nullable)
message         - پیام
status          - وضعیت (pending, responded, closed)
ip              - آدرس IP
session_id      - شناسه نشست
device          - User-Agent
responded_at    - تاریخ پاسخ (میلادی - خودکار تبدیل به جلالی)
created_at      - تاریخ (میلادی - خودکار تبدیل به جلالی)
updated_at      - تاریخ (میلادی - خودکار تبدیل به جلالی)
```

#### جدول: `advertisements`
```
...
views_count     - تعداد بازدید‌ها
contacts_count  - تعداد تماس‌های واردشده
...
```

### استفاده در کد

#### بازدید ثبت کردن

```php
use Modules\Advertisements\Services\ViewService;

$viewService = app(ViewService::class);

$recorded = $viewService->recordView(
    userId: $userId,           // شناسه کاربر (null برای مهمان)
    advertisementId: $adId,    // شناسه تبلیغ
    ip: $request->ip(),        // IP
    device: $request->header('User-Agent'),  // User-Agent
    sessionId: $request->session()->getId()  // شناسه نشست
);

if ($recorded) {
    // بازدید ثبت شد
}
```

#### تماس ثبت کردن

```php
use Modules\Advertisements\Services\ContactService;

$contactService = app(ContactService::class);

$recorded = $contactService->recordContact(
    advertisementId: $adId,
    userId: $request->user()?->id,
    name: 'علی احمدی',
    email: 'ali@example.com',
    phone: '09123456789',
    message: 'سلام، این کالا را می‌خواهم',
    ip: $request->ip(),
    device: $request->header('User-Agent'),
    sessionId: $request->session()->getId()
);

if ($recorded) {
    // تماس ثبت شد
}
```

#### دریافت تاریخ جلالی

```php
$contact = AdvertisementContact::find(1);

// دریافت تاریخ جلالی
echo $contact->created_at;  // خروجی: 1405-04-24

// دریافت فرمت‌شده
echo $contact->getCreatedAtJalaliFormatted();
// خروجی: 24 مهر 1405 14:30
```

#### شمارش بازدید و تماس

```php
$ad = Advertisement::find(1);

echo $ad->views_count;     // تعداد بازدید‌ها
echo $ad->contacts_count;  // تعداد تماس‌های واردشده

// افزایش دستی
$ad->incrementViewsCount();
$ad->incrementContactsCount();
```

## Middleware: بازدید خودکار

### موقعیت فایل
```
Modules/Advertisements/Http/Middleware/RecordAdvertisementView.php
```

### نحوه کار

وقتی کاربری صفحه تبلیغ را بازدید می‌کند:

1. Middleware بررسی می‌کند که آیا UUID تبلیغ در route وجود دارد
2. تبلیغ را دریافت می‌کند
3. تاریخ آخرین بازدید را در Cache بررسی می‌کند (deduplication)
4. اگر بازدید جدید است، ثبت می‌کند
5. `views_count` را افزایش می‌دهد

### مثال Route

```php
Route::get('/ads/{uuid}', function (Request $request, $uuid) {
    // بازدید خودکار ثبت می‌شود
    return view('ads.detail', ['uuid' => $uuid]);
})
->middleware('record.advertisement.view');
```

## مهاجرت دیتابیسی

### اجرای مهاجرات

```bash
php artisan migrate
```

### مهاجرات مرتبط

```
2026_07_15_000004_add_counters_to_advertisements_table.php
2026_07_15_000005_create_advertisement_contacts_table.php
```

## بهترین روش‌ها

### 1. همیشه از Trait استفاده کنید
```php
// ✅ صحیح
use HasJalaliDates;
protected $jalaliDates = ['published_at'];

$ad->published_at = '1405-04-24';  // خودکار تبدیل
```

### 2. برای جستجو، تبدیل کنید
```php
// ✅ صحیح
$gregorian = HasJalaliDates::jalaliToGregorian($userInput);
$ads = Advertisement::where('published_at', '>=', $gregorian)->get();

// ❌ غلط
$ads = Advertisement::where('published_at', '1405-04-24')->get();
```

### 3. برای نمایش، استفاده از متدهای Helper کنید
```php
// ✅ صحیح
echo $ad->getPublishedAtJalaliFormatted();

// ❌ غلط
echo $ad->published_at->format('Y-m-d');  // میلادی را نمایش می‌دهد
```

### 4. Middleware را ثبت کنید
```php
// در routes یا middleware کنفیگ
Route::middleware('record.advertisement.view')->group(function () {
    Route::get('/ads/{uuid}', [AdvertisementController::class, 'show']);
});
```

## خطاهای رایج

### خطای 1: فراموش کردن تعریف jalaliDates
```php
// ❌ خطا - تاریخ تبدیل نمی‌شود
class Advertisement extends Model {
    use HasJalaliDates;
    // jalaliDates تعریف نشده!
}

// ✅ درست
class Advertisement extends Model {
    use HasJalaliDates;
    protected $jalaliDates = ['published_at', 'created_at'];
}
```

### خطای 2: جستجو بدون تبدیل
```php
// ❌ خطا - نتیجه خالی برمی‌گرداند
$ads = Advertisement::where('published_at', '1405-04-24')->get();

// ✅ درست
$gregorian = HasJalaliDates::jalaliToGregorian('1405-04-24');
$ads = Advertisement::where('published_at', '>=', $gregorian)->get();
```

## مرجع API

### کلاس: HasJalaliDates

#### متدهای Static

| متد | توضیح |
|-----|-------|
| `gregorianToJalali($date)` | تبدیل تاریخ میلادی به جلالی |
| `jalaliToGregorian($jalaliDate)` | تبدیل تاریخ جلالی به میلادی |
| `formatJalaliDate($jalaliDate, $format)` | فرمت‌کردن تاریخ جلالی با نام‌های فارسی |

#### متدهای Instance

| متد | توضیح |
|-----|-------|
| `getJalaliDate($attribute)` | دریافت تاریخ جلالی |
| `getJalaliDateFormatted($attribute, $format)` | دریافت تاریخ جلالی فرمت‌شده |
| `setJalaliDate($attribute, $jalaliDate)` | تنظیم تاریخ از ورودی جلالی |

#### متدهای مختص Advertisement

| متد | توضیح |
|-----|-------|
| `getPublishedAtJalali()` | تاریخ انتشار (جلالی) |
| `getPublishedAtJalaliFormatted()` | تاریخ انتشار (جلالی + نام ماه) |
| `getExpiresAtJalali()` | تاریخ انقضاء (جلالی) |
| `getExpiresAtJalaliFormatted()` | تاریخ انقضاء (جلالی + نام ماه) |

## پایان

برای سوالات بیشتر یا گزارش خطا، لطفاً در مخزن پروژه Issue ایجاد کنید.

**نویسنده**: سیستم توسعه
**تاریخ آخرین بروز رسانی**: 1405-04-24
