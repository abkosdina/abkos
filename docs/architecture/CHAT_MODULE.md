# Chat Module Architecture

این سند توضیح می‌دهد چگونه ماژول چت در معماری ماژولار پروژه پیاده‌سازی شده و کدام قسمت‌ها فعال هستند.

## هدف

ماژول چت وظیفه ارائه زیرساخت مکالمه داخلی، ذخیره پیام، پیوست‌ها و مدیریت شرکت‌کنندگان را دارد. این ماژول باید مستقل باشد و از انتزاع service/repository برای جداسازی منطقی استفاده کند.

## ساختار ماژول

```text
Modules/Chat/
  Config/
  Database/
    Migrations/
  Models/
  Interfaces/
  Repositories/
    Eloquent/
  Services/
  Http/
    Controllers/
  Requests/
  Routes/
  Providers/
  README.md
```

## ثبت ماژول

- `Modules\Chat\Providers\ChatServiceProvider` در `app/Providers/AppServiceProvider.php` ثبت شده است.
- این provider:
  - config ماژول را با `mergeConfigFrom()` بارگذاری می‌کند.
  - مسیرهای `api/v1/chat` را با `loadRoutesFrom()` ثبت می‌کند.
  - مهاجرت‌های `Database/Migrations` را با `loadMigrationsFrom()` معرفی می‌کند.
  - پیاده‌سازی repositoryها را به رابط‌های مربوطه وصل می‌کند.
  - `ChatService` را در قالب تک‌نمونه (`singleton`) ثبت می‌کند و آن را به `ChatServiceInterface` علامت‌گذاری می‌کند.

## مدل‌ها و جداول پایگاه داده

- `chat_rooms`
  - نگه‌دارنده کانال‌های گفتگویی
  - `uuid`, `name`, `room_type`, `status`, `created_by`
  - `softDeletes`
  - تاریخچه audit برای `created_by`, `updated_by`, `deleted_by`

- `chat_messages`
  - پیام‌های هر کانال
  - `chat_room_id`, `sender_id`, `message`, `message_type`, `status`, `read_at`
  - ارتباط با `ChatAttachment`
  - `softDeletes`

- `chat_attachments`
  - پیوست‌های پیام
  - `chat_message_id`, `file_path`, `mime_type`, `size_bytes`

- `chat_participants`
  - کاربران عضو هر کانال
  - `chat_room_id`, `user_id`, `role`, `joined_at`

- `chat_message_reads`
  - ثبت خوانده شدن پیام برای کاربر
  - `chat_message_id`, `user_id`, `read_at`

## پیاده‌سازی

### Repository

هر repository یک interface و یک پیاده‌سازی Eloquent دارد:
- `ChatRoomRepositoryInterface`
- `ChatMessageRepositoryInterface`
- `ChatAttachmentRepositoryInterface`
- `ChatParticipantRepositoryInterface`
- `ChatMessageReadRepositoryInterface`

پیاده‌سازی‌ها وظیفه CRUD و بازیابی داده‌ها را دارند.

### Service

`Modules\Chat\Services\ChatService` وظایف زیر را انجام می‌دهد:
- لیست کانال‌های چت برای کاربر
- ایجاد کانال چت جدید و ثبت شرکت‌کنندگان
- دریافت جزئیات اتاق
- لیست پیام‌های یک اتاق
- ارسال پیام جدید همراه با پیوست‌ها
- علامت‌گذاری پیام‌ها به‌عنوان خوانده شده

این سرویس از repositoryها استفاده می‌کند تا منطق تجاری از دیتابیس جدا بماند.

### Controller

`Modules\Chat\Http\Controllers\ChatController` مسئول دریافت درخواست‌ها و بازگرداندن پاسخ JSON است. عملیات واقعی به Service واگذار می‌شود.

### Validation

درخواست‌های ورودی با FormRequest اعتبارسنجی می‌شوند:
- `CreateChatRoomRequest`
- `SendChatMessageRequest`

## مسیرهای API

تمام مسیرها تحت مسیر `api/v1/chat` و middleware `auth:sanctum` قرار دارند:

- `GET /api/v1/chat/rooms`
- `POST /api/v1/chat/rooms`
- `GET /api/v1/chat/rooms/{room}`
- `GET /api/v1/chat/rooms/{room}/messages`
- `POST /api/v1/chat/rooms/{room}/messages`
- `POST /api/v1/chat/rooms/{room}/mark-read`
- `POST /api/v1/chat/messages/{message}/attachments`

## محدودیت‌های فعلی

- endpoint پیوست‌ها (`addAttachment`) فقط وضعیت placeholder دارد و هنوز ذخیره‌سازی واقعی فایل در آن اجرا نشده.
- هنوز policies و authorization دقیق روی منابع chat تعریف نشده است.
- تنها سطح حفاظت فعلی، middleware `auth:sanctum` است.

## Test Coverage

- تست‌های feature در `Modules/Chat/Tests/Feature/ChatModuleTest.php` اضافه شده‌اند.
- تست‌های فعلی شامل:
  - ایجاد اتاق چت
  - لیست کردن اتاق‌ها
  - ارسال پیام
  - مشاهده پیام‌های یک اتاق
  - علامت‌گذاری پیام‌ها به‌عنوان خوانده شده
  - نمایش جزئیات اتاق
  - endpoint پلاسیهولدر پیوست‌ها

## نکات توسعه‌ای

- بهتر است در آینده `ChatRoomPolicy` و `ChatMessagePolicy` تعریف شود.
- پیوست‌ها باید به storage و قوانین upload امن متصل شوند.
- برای عملکرد بهتر، pagination و محدودیت‌های query برای پیام‌ها افزوده شود.

## آخرین بروزرسانی

- 2026-07-18: ایجاد مستند جداگانه معماری ماژول چت و توضیح دقیق ساختار، مسیرها و وضعیت فعلی.
