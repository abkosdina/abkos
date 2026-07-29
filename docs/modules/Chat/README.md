# Chat Module

این ماژول پیاده‌سازی اولیه زیرساخت چت را در معماری ماژولار پروژه فراهم می‌کند.

## وضعیت فعلی

- ثبت `Modules\Chat\Providers\ChatServiceProvider` در `app/Providers/AppServiceProvider.php`
- بارگذاری مسیرهای API خصوصی با `auth:sanctum`
- بارگذاری مهاجرت‌های ماژول از `Modules/Chat/Database/Migrations`
- استفاده از الگوی Repository -> Service -> Controller
- پیاده‌سازی مدل‌های Eloquent برای `chat_rooms`, `chat_messages`, `chat_attachments`, `chat_participants`, `chat_message_reads`
- پیاده‌سازی درخواست‌های اعتبارسنجی با `CreateChatRoomRequest` و `SendChatMessageRequest`

## ساختار ماژول

- `Config/chat.php`
- `Database/Migrations/2026_07_18_000001_create_chat_module_tables.php`
- `Models/ChatRoom.php`
- `Models/ChatMessage.php`
- `Models/ChatAttachment.php`
- `Models/ChatParticipant.php`
- `Models/ChatMessageRead.php`
- `Repositories/Interfaces/` *(قراردادهای Repository)*
- `Repositories/Eloquent/` *(پیاده‌سازی‌های Eloquent)*
- `Services/ChatService.php`
- `Interfaces/ChatServiceInterface.php`
- `Http/Controllers/ChatController.php`
- `Requests/CreateChatRoomRequest.php`
- `Requests/SendChatMessageRequest.php`
- `Routes/api.php`

## مدل‌های اصلی

- `ChatRoom` - کانال‌های چت
- `ChatMessage` - پیام‌های ارسال شده در کانال
- `ChatAttachment` - فایل‌های پیوست پیام
- `ChatParticipant` - کاربران عضو هر کانال
- `ChatMessageRead` - وضعیت خوانده شدن پیام برای هر کاربر

## جریان کاری

1. درخواست از `ChatController` گرفته می‌شود.
2. Controller به `ChatServiceInterface` وابسته است.
3. `ChatServiceProvider` پیاده‌سازی `ChatService` را ثبت می‌کند و آن را به `ChatServiceInterface` وصل می‌کند.
4. سرویس از repositoryهای مربوطه برای عملیات دیتابیس استفاده می‌کند.

## مسیرهای API

تمام مسیرها با `auth:sanctum` محافظت شده‌اند:

- `GET /api/v1/chat/rooms` - لیست اتاق‌های چت کاربر
- `POST /api/v1/chat/rooms` - ایجاد اتاق چت جدید
- `GET /api/v1/chat/rooms/{room}` - مشاهده جزئیات اتاق
- `GET /api/v1/chat/rooms/{room}/messages` - دریافت پیام‌های اتاق
- `POST /api/v1/chat/rooms/{room}/messages` - ارسال پیام جدید
- `POST /api/v1/chat/rooms/{room}/mark-read` - علامت‌گذاری پیام‌های اتاق به‌عنوان خوانده‌شده
- `POST /api/v1/chat/messages/{message}/attachments` - نقطه پلاسیهولدر برای پیوست‌های پیام

## نکات پیاده‌سازی

- تابع‌های `UUID` با `Str::uuid()` تولید می‌شوند.
- ستون‌های `created_by`, `updated_by`, `deleted_by` برای ردیابی تغییرات افزوده شده‌اند.
- `ChatRoomRepository` اتاق‌هایی را بازمی‌گرداند که کاربر در آن‌ها عضو است.
- `ChatMessageRepository` پیام‌ها را بر اساس `chat_room_id` بازیابی می‌کند.
- `ChatMessageReadRepository` خواندن پیام را برای کاربر ثبت یا بروزرسانی می‌کند.

## محدودیت‌های فعلی

- endpoint بارگذاری پیوست‌ها (`addAttachment`) فعلاً پلاسیهولدر است و باید برای ذخیره‌سازی فایل واقعی تکمیل شود.
- احراز هویت و دسترسی به اتاق‌ها فقط از طریق middleware محافظت شده است و هنوز policy خاصی تعریف نشده.

## راه‌اندازی سریع

1. مهاجرت‌ها را اجرا کنید:

```bash
php artisan migrate
```

2. اطمینان حاصل کنید `ChatServiceProvider` در `app/Providers/AppServiceProvider.php` ثبت شده است.

3. درخواست‌های API را با `Authorization: Bearer <token>` ارسال کنید.

## تست‌ها

- تست‌های feature ماژول در `Modules/Chat/Tests/Feature/ChatModuleTest.php` قرار دارند.
- تست‌های موجود شامل ایجاد اتاق، لیست اتاق‌ها، ارسال پیام، مشاهده جزئیات اتاق، علامت‌گذاری خوانده‌شده و endpoint پلاسیهولدر پیوست هستند.

## مستندات مرتبط

- `docs/architecture/CHAT_MODULE.md` - معماری ماژول چت
- `docs/IMPLEMENTATION_SUMMARY.md` - خلاصه وضعیت و پیاده‌سازی

## آخرین بروزرسانی

- 2026-07-18: ثبت کامل ماژول چت با مسیرها، سرویس، repositoryها، migration، تست‌ها و مستندات اولیه.

