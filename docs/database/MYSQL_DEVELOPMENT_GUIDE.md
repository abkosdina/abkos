# راهنمای توسعه با MySQL

## 1. پیش‌نیازها
- XAMPP یا MariaDB/MySQL نصب باشد.
- PHP و Composer آماده باشند.
- پشتیبانی PDO MySQL در PHP فعال باشد.

## 2. تنظیم محیط
1. از فایل نمونه یک فایل .env بسازید:
   - copy .env.example .env
2. در فایل .env تنظیمات زیر را بررسی کنید:
   - DB_CONNECTION=mysql
   - DB_HOST=127.0.0.1
   - DB_PORT=3306
   - DB_DATABASE=radio
   - DB_USERNAME=root
   - DB_PASSWORD=

## 3. ساخت پایگاه‌داده
در MySQL یک دیتابیس به نام radio بسازید:

```sql
CREATE DATABASE radio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 4. اجرای مهاجرت‌ها
```bash
php artisan migrate
```

## 5. اگر نیاز به داده اولیه بود
```bash
php artisan db:seed
```

## 6. نکات مهم
- این پروژه از معماری ماژولار استفاده می‌کند و ماژول‌های اصلی از طریق ServiceProvider ثبت می‌شوند.
- برای توسعه مالی، ابتدا ماژول‌های Ledger و Wallet را بررسی کنید.
- در صورت بروز خطای اتصال، از تنظیمات پورت و نام کاربری/رمز عبور MySQL مطمئن شوید.
