# سیستم بازدید و تماس تبلیغات

## مقدمه

این سند شرح می‌دهد چگونه سیستم بازدید (Views) و تماس (Contacts) برای تبلیغات پیاده‌سازی شده است. این سیستم اجازه می‌دهد تا:

1. **ثبت خودکار بازدید**: هر بار کاربری تبلیغ را بازدید می‌کند، تعداد بازدید افزایش می‌یابد
2. **ثبت تماس**: کاربران می‌توانند با فروشنده تبلیغ تماس بگیرند
3. **شمارش و آمار**: تعداد بازدید و تماس‌ها در دسترس است

## معماری سیستم

```
┌─────────────────────────────────────────────────────────────┐
│                    کاربر/بازدیدکننده                        │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
        ┌────────────────────────┐
        │  بازدید صفحه تبلیغ    │
        │  (GET /ads/{uuid})     │
        └────────┬───────────────┘
                 │
                 ▼
    ┌─────────────────────────────────────┐
    │  RecordAdvertisementView Middleware │
    └──────────────┬──────────────────────┘
                   │
                   ▼
         ┌──────────────────────┐
         │   ViewService        │
         │   - Deduplication    │
         │   - Record View      │
         └──────┬───────────────┘
                │
       ┌────────┴────────┐
       ▼                 ▼
┌─────────────┐   ┌──────────────────┐
│ Cache Check │   │ Insert into DB   │
│ (60 min)    │   │ advertisement_   │
└─────────────┘   │ views table      │
       ▲          └────┬─────────────┘
       │               │
       │               ▼
       │         ┌──────────────────────┐
       └─────────│ Increment views_     │
                 │ count on ad          │
                 └──────────────────────┘
```

## جداول پایگاه داده

### 1. جدول advertisement_views
```sql
CREATE TABLE advertisement_views (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULLABLE,           -- شناسه کاربر (null برای مهمان‌ها)
    advertisement_id BIGINT NOT NULL,  -- شناسه تبلیغ
    ip VARCHAR(45) NULLABLE,           -- آدرس IP
    device TEXT NULLABLE,              -- User-Agent
    session_id VARCHAR(255) NULLABLE,  -- شناسه نشست
    created_at TIMESTAMP,              -- تاریخ ایجاد (میلادی)
    updated_at TIMESTAMP               -- تاریخ آخرین تغییر (میلادی)
);
```

### 2. جدول advertisement_contacts
```sql
CREATE TABLE advertisement_contacts (
    id BIGINT PRIMARY KEY,
    advertisement_id BIGINT NOT NULL,  -- شناسه تبلیغ
    user_id BIGINT NULLABLE,           -- شناسه کاربر (null برای مهمان‌ها)
    name VARCHAR(255) NOT NULL,        -- نام تماس‌گیرنده
    email VARCHAR(255) NOT NULL,       -- ایمیل
    phone VARCHAR(20) NULLABLE,        -- شماره تماس
    message TEXT NOT NULL,             -- پیام
    status VARCHAR(50) DEFAULT 'pending', -- وضعیت (pending/responded/closed)
    ip VARCHAR(45) NULLABLE,           -- آدرس IP
    session_id VARCHAR(255) NULLABLE,  -- شناسه نشست
    device TEXT NULLABLE,              -- User-Agent
    responded_at TIMESTAMP NULLABLE,   -- تاریخ پاسخ (میلادی)
    created_at TIMESTAMP,              -- تاریخ ایجاد (میلادی)
    updated_at TIMESTAMP               -- تاریخ آخرین تغییر (میلادی)
);
```

### 3. ستون‌های جدید در جدول advertisements
```sql
ALTER TABLE advertisements ADD COLUMN (
    views_count BIGINT DEFAULT 0,      -- تعداد بازدید‌ها
    contacts_count BIGINT DEFAULT 0    -- تعداد تماس‌های واردشده
);
```

## بخش اول: سیستم بازدید (Views)

### خدمات (Services)

#### ViewService
```php
namespace Modules\Advertisements\Services;

class ViewService
{
    /**
     * ثبت بازدید جدید
     * 
     * @param int|null $userId - شناسه کاربر (null برای مهمان‌ها)
     * @param int $advertisementId - شناسه تبلیغ
     * @param string|null $ip - آدرس IP
     * @param string|null $device - User-Agent
     * @param string|null $sessionId - شناسه نشست
     * @return bool
     */
    public function recordView(
        ?int $userId,
        int $advertisementId,
        ?string $ip = null,
        ?string $device = null,
        ?string $sessionId = null
    ): bool;
}
```

#### Deduplication (حذف تکرار)
- هر کاربر فقط یک بار در هر 60 دقیقه می‌تواند بازدید ثبت کند
- کلید deduplication: `md5(ad_id:user_id:ip:session_id)`
- ذخیره در Cache برای سرعت بیشتر
- قابل تنظیم: `AD_VIEW_DEDUPE_MINUTES` env

### استفاده در Controller

```php
use Modules\Advertisements\Services\ViewService;

class AdvertisementController
{
    public function show(Request $request, Advertisement $advertisement)
    {
        $viewService = app(ViewService::class);
        
        // ثبت بازدید
        $viewService->recordView(
            userId: $request->user()?->id,
            advertisementId: $advertisement->id,
            ip: $request->ip(),
            device: $request->header('User-Agent'),
            sessionId: $request->session()?->getId()
        );
        
        return response()->json($advertisement);
    }
}
```

### استفاده در Middleware

```php
namespace Modules\Advertisements\Http\Middleware;

class RecordAdvertisementView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $uuid = $request->route('uuid');
        if ($uuid) {
            $advertisement = Advertisement::where('uuid', $uuid)->first();
            if ($advertisement) {
                app(ViewService::class)->recordView(
                    $request->user()?->id,
                    $advertisement->id,
                    $request->ip(),
                    $request->header('User-Agent'),
                    $request->session()?->getId()
                );
            }
        }
        
        return $response;
    }
}
```

### ثبت Middleware در Route

```php
// در فایل routes/api.php یا routes/web.php
Route::middleware('record.advertisement.view')->group(function () {
    Route::get('/ads/{uuid}', [AdvertisementController::class, 'show']);
});
```

### Events (رویدادها)

```php
namespace Modules\Advertisements\Events;

class AdvertisementViewed
{
    public function __construct(
        public ?int $userId,
        public int $advertisementId,
        public ?string $ip,
        public ?string $device
    ) {}
}
```

### دریافت آمار بازدید

```php
$ad = Advertisement::find(1);

// تعداد کل بازدید‌ها
echo $ad->views_count;

// افزایش دستی (اگر لازم باشد)
$ad->incrementViewsCount();

// دریافت تمام بازدید‌های یک تبلیغ
$views = $ad->views()->get();
echo count($views);

// آمار بازدید برای بازدیدکنندگان ثبت‌نام‌شده
$userViews = $ad->views()->whereNotNull('user_id')->count();

// آمار بازدید امروز (میلادی)
$todayViews = $ad->views()
    ->whereDate('created_at', today())
    ->count();

// آمار بازدید روز جاری (جلالی)
$today = \Carbon\Carbon::now()->toDateString();  // 2026-07-15
$todayViews = $ad->views()
    ->whereDate('created_at', $today)
    ->count();
```

## بخش دوم: سیستم تماس (Contacts)

### خدمات (Services)

#### ContactService
```php
namespace Modules\Advertisements\Services;

class ContactService
{
    /**
     * ثبت تماس جدید
     * 
     * @param int $advertisementId - شناسه تبلیغ
     * @param int|null $userId - شناسه کاربر (null برای مهمان‌ها)
     * @param string $name - نام تماس‌گیرنده
     * @param string $email - ایمیل
     * @param string|null $phone - شماره تماس
     * @param string $message - پیام
     * @param string|null $ip - آدرس IP
     * @param string|null $device - User-Agent
     * @param string|null $sessionId - شناسه نشست
     * @return bool
     */
    public function recordContact(
        int $advertisementId,
        ?int $userId,
        string $name,
        string $email,
        ?string $phone,
        string $message,
        ?string $ip = null,
        ?string $device = null,
        ?string $sessionId = null
    ): bool;
}
```

### Controller

```php
namespace Modules\Advertisements\Http\Controllers;

class ContactController
{
    public function store(
        Advertisement $advertisement,
        StoreAdvertisementContactRequest $request,
        ContactService $contactService
    ): JsonResponse {
        $validated = $request->validated();
        
        $success = $contactService->recordContact(
            advertisementId: $advertisement->id,
            userId: $request->user()?->id,
            name: $validated['name'],
            email: $validated['email'],
            phone: $validated['phone'] ?? null,
            message: $validated['message'],
            ip: $request->ip(),
            device: $request->header('User-Agent'),
            sessionId: $request->session()?->getId()
        );
        
        return response()->json([
            'message' => $success ? 'تماس با موفقیت ثبت شد' : 'خطا در ثبت تماس',
        ], $success ? 201 : 500);
    }
}
```

### Request Validation

```php
namespace Modules\Advertisements\Requests;

class StoreAdvertisementContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'نام الزامی است',
            'email.required' => 'ایمیل الزامی است',
            'email.email' => 'ایمیل معتبر نیست',
            'message.required' => 'پیام الزامی است',
            'message.min' => 'پیام حداقل 10 کاراکتر باید باشد',
        ];
    }
}
```

### Events (رویدادها)

```php
namespace Modules\Advertisements\Events;

class AdvertisementContactCreated
{
    public function __construct(
        public AdvertisementContact $contact
    ) {}
}
```

### Model: AdvertisementContact

```php
namespace Modules\Advertisements\Models;

class AdvertisementContact extends Model
{
    use HasJalaliDates;
    
    protected $jalaliDates = [
        'created_at',
        'updated_at',
        'responded_at',
    ];
    
    // روابط
    public function advertisement() { /* ... */ }
    public function user() { /* ... */ }
    
    // Scopes
    public function scopePending($query) { /* ... */ }
    public function scopeResponded($query) { /* ... */ }
    public function scopeClosed($query) { /* ... */ }
    public function scopeForAdvertisement($query, $adId) { /* ... */ }
    
    // متدها
    public function markAsResponded() { /* ... */ }
    public function markAsClosed() { /* ... */ }
}
```

### استفاده در کد

#### ثبت تماس

```php
use Modules\Advertisements\Services\ContactService;

$contactService = app(ContactService::class);

$success = $contactService->recordContact(
    advertisementId: 1,
    userId: null,  // مهمان
    name: 'علی احمدی',
    email: 'ali@example.com',
    phone: '09123456789',
    message: 'سلام، این کالا را می‌خواهم',
    ip: '192.168.1.1',
    device: 'Mozilla/5.0...',
    sessionId: 'abc123'
);
```

#### دریافت تماس‌های تبلیغ

```php
$ad = Advertisement::find(1);

// تمام تماس‌های این تبلیغ
$contacts = $ad->contacts()->get();

// فقط تماس‌های در انتظار پاسخ
$pendingContacts = $ad->contacts()->pending()->get();

// فقط تماس‌های پاسخ‌شده
$respondedContacts = $ad->contacts()->responded()->get();

// تعداد تماس‌ها
echo $ad->contacts_count;

// تماس‌های روز جاری
$todayContacts = $ad->contacts()
    ->whereDate('created_at', today())
    ->get();
```

#### مدیریت وضعیت تماس

```php
$contact = AdvertisementContact::find(1);

// نمایش اطلاعات تماس
echo $contact->name;     // علی احمدی
echo $contact->email;    // ali@example.com
echo $contact->message;  // پیام
echo $contact->status;   // pending

// علامت‌گذاری به‌عنوان پاسخ‌شده
$contact->markAsResponded();

// علامت‌گذاری به‌عنوان بسته‌شده
$contact->markAsClosed();

// دریافت تاریخ جلالی
echo $contact->getCreatedAtJalaliFormatted();
// خروجی: 24 مهر 1405 14:30
```

#### جستجو و فیلتر

```php
// تماس‌های یک کاربر
$userContacts = AdvertisementContact::where('user_id', $userId)->get();

// تماس‌های از یک ایمیل خاص
$contacts = AdvertisementContact::where('email', 'ali@example.com')->get();

// تماس‌های یک IP خاص
$ipContacts = AdvertisementContact::where('ip', '192.168.1.1')->get();

// تماس‌های در یک بازه زمانی
use App\Traits\HasJalaliDates;

$startDate = HasJalaliDates::jalaliToGregorian('1405-01-01');
$endDate = HasJalaliDates::jalaliToGregorian('1405-12-29');

$contacts = AdvertisementContact::whereBetween('created_at', [$startDate, $endDate])->get();
```

## API Routes

### بازدید

```php
// بازدید خودکار از طریق Middleware
GET /ads/{uuid}
Middleware: record.advertisement.view
```

### تماس

```php
// ثبت تماس جدید
POST /api/advertisements/{id}/contacts
Request Body:
{
    "name": "علی احمدی",
    "email": "ali@example.com",
    "phone": "09123456789",
    "message": "سلام، این کالا را می‌خواهم"
}

Response:
{
    "message": "Contact inquiry submitted successfully",
    "data": {
        "advertisement_id": 1,
        "status": "pending"
    }
}
```

## Events و Listeners

### رویداد بازدید: AdvertisementViewed
```php
// استفاده در Listener
Event::listen(AdvertisementViewed::class, function (AdvertisementViewed $event) {
    // ارسال اطلاع، آپدیت کش، و ...
    Cache::forget('ad.views.' . $event->advertisementId);
});
```

### رویداد تماس: AdvertisementContactCreated
```php
// استفاده در Listener
Event::listen(AdvertisementContactCreated::class, function (AdvertisementContactCreated $event) {
    // ارسال ایمیل به فروشنده
    // ثبت در سیستم notification
    // و ...
    Mail::send(new NewContactNotification($event->contact));
});
```

## بهترین روش‌ها

### 1. همیشه از Services استفاده کنید
```php
// ✅ صحیح
$viewService->recordView(...);
$contactService->recordContact(...);

// ❌ غلط
DB::table('advertisement_views')->insert(...);
```

### 2. Deduplication را فعال نگه دارید
```php
// ✅ صحیح - یک بار در 60 دقیقه ثبت می‌شود
$viewService->recordView($userId, $adId, $ip, $device, $sessionId);

// ❌ غلط - هربار ثبت می‌شود
DB::table('advertisement_views')->insert([...]);
```

### 3. Validation را انجام دهید
```php
// ✅ صحیح
$validated = $request->validated();
$contactService->recordContact(...);

// ❌ غلط
$contactService->recordContact($request->all());
```

### 4. Middleware را استفاده کنید
```php
// ✅ صحیح
Route::middleware('record.advertisement.view')->get('/ads/{uuid}', ...);

// ❌ غلط
// بازدید در Controller ثبت کنید (فراموش‌کردن‌ها ممکن است)
```

## مهاجرت

### اجرای مهاجرات
```bash
php artisan migrate
```

### فایل‌های مهاجرت
```
2026_07_15_000004_add_counters_to_advertisements_table.php
2026_07_15_000005_create_advertisement_contacts_table.php
```

### Rollback
```bash
php artisan migrate:rollback
```

## مثال‌های کامل

### مثال 1: نمایش آمار در API

```php
$ad = Advertisement::find(1);

return response()->json([
    'id' => $ad->id,
    'title' => $ad->title,
    'statistics' => [
        'views_count' => $ad->views_count,
        'contacts_count' => $ad->contacts_count,
    ],
    'dates' => [
        'published_at' => $ad->getPublishedAtJalaliFormatted(),
        'created_at' => $ad->getCreatedAtJalaliFormatted(),
    ],
]);

// خروجی:
{
    "id": 1,
    "title": "تبلیغ نمونه",
    "statistics": {
        "views_count": 150,
        "contacts_count": 8
    },
    "dates": {
        "published_at": "24 مهر 1405 14:30",
        "created_at": "20 مهر 1405 10:15"
    }
}
```

### مثال 2: Dashboard فروشنده

```php
$seller = auth()->user();

$ads = $seller->advertisements()
    ->with(['contacts' => function ($query) {
        $query->pending();
    }])
    ->select(['id', 'title', 'views_count', 'contacts_count'])
    ->get();

return response()->json([
    'ads' => $ads->map(fn($ad) => [
        'id' => $ad->id,
        'title' => $ad->title,
        'views' => $ad->views_count,
        'contacts' => $ad->contacts_count,
        'pending_inquiries' => $ad->contacts->count(),
    ]),
]);
```

### مثال 3: ارسال اطلاع

```php
// در Listener برای AdvertisementContactCreated
public function handle(AdvertisementContactCreated $event)
{
    $contact = $event->contact;
    $seller = $contact->advertisement->user;
    
    // ارسال ایمیل
    Mail::send(new NewContactNotification($contact), to: $seller->email);
    
    // ایجاد notification
    $seller->notify(new NewContactNotification($contact));
}
```

## خطاهای رایج و حل‌ها

| خطا | دلیل | حل |
|-----|------|-----|
| بازدید ثبت نمی‌شود | Middleware ثبت نشده | Middleware را در routes ثبت کنید |
| شماره‌های جوری | Deduplication کار نمی‌کند | Cache را بررسی کنید |
| Validation Fail | درخواست اطلاعات ناقص | تمام فیلدهای لازم را ارسال کنید |
| تاریخ‌های غلط | Jalali Dates مشخص نشده | `jalaliDates` array را تعریف کنید |

## پایان

برای سوالات بیشتر یا گزارش خطا، لطفاً Issue ایجاد کنید.

**نویسنده**: سیستم توسعه
**تاریخ آخرین بروز رسانی**: 1405-04-24
