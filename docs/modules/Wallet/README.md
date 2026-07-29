# Wallet Module

ماژول Wallet مسئول مدیریت کیف پول داخلی کاربران است و به‌صورت مستقیم با ماژول Ledger برای ثبت تراکنش‌های مالی و حفظ یکپارچگی حساب‌ها تعامل می‌کند.

## هدف و مسئولیت‌ها

- مدیریت چرخه‌ی عمر کیف پول کاربر
- نمایش موجودی‌ها و وضعیت کیف پول
- ایجاد و نگهداری تراکنش‌های کیف پول
- انجام عملیات سپرده، برداشت و انتقال داخلی
- پشتیبانی از قفل کردن موجودی و اعمال محدودیت‌های مالی
- تولید تاریخچه قابل بازبینی برای پاسخگویی و گزارش‌دهی
- حفظ اصل: موجودی کیف پول هرگز به‌صورت مستقیم تغییر نمی‌کند؛ همه تغییرات مالی از طریق Ledger ثبت می‌شوند.

## ساختار ماژول

- `Config/` — پیکربندی‌های wallet مانند نوع پیش‌فرض، حساب آفسِت دفتر کل و محدودیت‌ها
- `Database/` — مایگریشن جدول‌های `wallets`, `wallet_balances`, `wallet_transactions`, `wallet_locks`, `wallet_limits`, `wallet_settings`
- `Models/` — Eloquent مدل‌های مرتبط با کیف پول
- `Repositories/` — الگو و پیاده‌سازی دسترسی به داده
- `Services/` — منطق دامنه شامل `WalletService`, `TransferService`, `BalanceService`, `WalletLockService`, `WalletLimitService`
- `Http/Controllers/` — کنترلرهای API و مدیریت ورودی
- `Routes/` — مسیرهای API و مسیرهای ادمین
- `Tests/` — تست‌های ویژگی و واحد برای اعتبارسنجی رفتار کیف پول

## روابط کلیدی

- `Wallet` به `User` تعلق دارد و دارای `WalletBalance`, `WalletTransaction`, `WalletLock`, `WalletLimit`, `WalletSetting` است.
- هر `Wallet` یک `ledger_account_id` دارد که به حساب دفتر کل مرتبط است.
- `WalletTransaction` به یک `ledger_transaction` و یک `financial_transaction` وصل می‌شود.
- `FinancialTransaction` رکورد کسب‌وکار تراکنش را نگه می‌دارد و می‌تواند بین چند `WalletTransaction` رابطه داشته باشد.

## موجودیت‌های اصلی

### Wallet

- `uuid`، `user_id`، `wallet_type`
- `ledger_account_id` برای اتصال به حساب دفتر کل
- `currency`، `status`
- `metadata`

### WalletBalance

- `available_balance`
- `locked_balance`
- `blocked_balance`
- `pending_balance`
- `total_balance`
- `currency`

### WalletTransaction

- `wallet_id`
- `ledger_transaction_id`
- `financial_transaction_id`
- `transaction_type`
- `amount`
- `status`
- `description`
- `metadata`

### FinancialTransaction

- `type`, `status`, `amount`, `currency`
- `sender_wallet_id`, `receiver_wallet_id`
- `reference_type`, `reference_id`
- `idempotency_key`
- `ledger_transaction_id`
- `metadata`

## جریان تراکنش

1. درخواست سپرده/برداشت/انتقال از کنترلر دریافت می‌شود.
2. `WalletService` یا `TransferService` وضعیت کیف پول را بررسی می‌کند.
3. ورودی‌های دفتر کل (`ledger_entries`) بر اساس نوع تراکنش ساخته می‌شوند.
4. `LedgerService` یک `LedgerTransaction` دوتایی ایجاد می‌کند.
5. `FinancialTransaction` به‌عنوان رکورد کسب‌وکار ساخته می‌شود.
6. یک یا چند `WalletTransaction` برای آثار کیف پول ثبت می‌شود.
7. `BalanceService` موجودی کیف پول را بازسازی و هماهنگ می‌کند.

## خدمات مهم

- `WalletService`
  - مدیریت کیف پول
  - ایجاد کیف پول
  - مشاهده کیف پول و موجودی
  - سپرده و برداشت
  - انتقال داخلی
  - قفل/بازکردن قفل موجودی
  - فریز و بستن کیف پول

- `TransferService`
  - اجرای انتقال امن بین کیف پول‌ها
  - ثبت دو رکورد کیف پول (مبدا/مقصد)
  - ایجاد `FinancialTransaction` و لینک به `LedgerTransaction`

- `BalanceService`
  - بازسازی مانده‌ی کیف پول
  - خواندن موجودی جاری

- `WalletLockService`
  - قفل کردن بخشی از موجودی برای سفارش‌ها یا تعهدها
  - آزادسازی قفل

- `WalletLimitService`
  - اعمال محدودیت‌های روزانه و حداکثر تراکنش

## API

مسیرهای اصلی در `Modules/Wallet/Routes/api.php` تعریف شده‌اند.

- `GET /api/v1/wallets` — لیست کیف پول‌ها
- `POST /api/v1/wallets` — ایجاد کیف پول جدید
- `GET /api/v1/wallets/{wallet}` — نمایش جزئیات کیف پول
- `GET /api/v1/wallets/{wallet}/balances` — مشاهده موجودی
- `POST /api/v1/wallets/{wallet}/deposit` — عملیات سپرده
- `POST /api/v1/wallets/{wallet}/withdraw` — عملیات برداشت
- `POST /api/v1/wallets/{wallet}/transfer` — انتقال داخلی
- `POST /api/v1/wallets/{wallet}/lock` — قفل کردن موجودی
- `POST /api/v1/wallets/{wallet}/unlock` — آزادسازی قفل
- `POST /api/v1/wallets/{wallet}/freeze` — فریز کردن کیف پول
- `POST /api/v1/wallets/{wallet}/close` — بستن کیف پول

## مسیرهای ادمین

- `POST /api/v1/admin/wallets/adjustments` — اصلاحات کیف پول توسط مدیر

## رابط با Ledger

- `Wallet` به عنوان لایه‌ی کاربردی پول دیجیتال عمل می‌کند و مقادیر مالی را مستقیماً تغییر نمی‌دهد.
- هر تراکنش کیف پول باید به یک `LedgerTransaction` دوتایی متصل باشد.
- این جداسازی تضمین می‌کند که:
  - حسابداری مالی دقیق و قابل بازبینی باشد
  - گزارشات مالی همیشه بر اساس دفتر کل واقعی تولید شوند
  - تغییرات بعدی فقط از طریق ورودی جدید و غیرقابل حذف اعمال شوند

## نکات پیکربندی

- فایل پیکربندی: `Modules/Wallet/Config/wallet.php`
- مقادیر مهم:
  - `wallet.default_type`
  - `wallet.ledger_offset_account_id`
  - `wallet.limits.daily_deposit`
  - `wallet.limits.daily_withdrawal`
  - `wallet.limits.maximum_balance`
  - `wallet.limits.maximum_transaction`

## نکات توسعه

- برای اجرای صحیح تست‌ها، مایگریشن‌های ماژول Wallet و Ledger باید در `tests/TestCase.php` بارگذاری شوند.
- `metadata` در مدل‌های کیف پول به صورت `array` کاست شده و باید از ورودی JSON قابل قبول استفاده شود.
- تمام تراکنش‌های مالی باید قابل بازتولید و idempotent باشند؛ به همین دلیل `FinancialTransaction` از `idempotency_key` پشتیبانی می‌کند.

## مسیرهای مرتبط

- `Modules/Wallet/Providers/WalletServiceProvider.php`
- `Modules/Wallet/Routes/api.php`
- `Modules/Wallet/Database/Migrations/2026_07_08_000001_create_wallet_module_tables.php`
- `Modules/Wallet/Models/Wallet.php`
- `Modules/Wallet/Models/WalletTransaction.php`
- `Modules/Wallet/Models/FinancialTransaction.php`
- `Modules/Wallet/Services/WalletService.php`
- `Modules/Wallet/Services/TransferService.php`

## تست‌ها

- `Modules/Wallet/Tests/Feature/WalletApiTest.php`
- `Modules/Wallet/Tests/Feature/WalletTransferTest.php`
- `Modules/Wallet/Tests/Feature/FinancialTransactionTest.php`

## خلاصه

ماژول Wallet در این پروژه نقش لایه‌ی عملکردی کیف پول را دارد و با تکیه بر `Ledger` به عنوان مرجع نهایی مالی، وضعیت‌های کاربر و گزارش تراکنش را مدیریت می‌کند.
