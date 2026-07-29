# Approval Engine Core Lifecycle

## نمای کلی

موتور تأیید عمومی در ماژول Workflow پیاده‌سازی شده است و برای جریان‌های مختلفی مانند تأیید تبلیغات، KYC، قراردادها، درخواست‌های مالی و سایر فرآیندهای کسب‌وکار قابل استفاده است.

## مسئولیت‌های اصلی

- شروع فرآیند تأیید برای یک WorkflowInstance
- ایجاد ApprovalInstance و ApprovalInstanceStep‌های اولیه بر اساس تعریف تأیید
- ارزیابی pre-conditions قبل از ثبت هر تصمیم
- پردازش تصمیم‌های approve، reject و return_for_correction
- حفظ حالت تراکنشی، ای‌پتای و جلوگیری از تصمیم‌های تکراری
- انتشار رویدادهای عمومی برای اتصال به لایه‌های دیگر

## چرخه زندگی

### 1. Start

در مرحله شروع، موتور یک ApprovalInstance ایجاد می‌کند و بر اساس ApprovalDefinition، گام‌های اولیه را به صورت فعال می‌سازد. این گام‌ها در وضعیت active قرار می‌گیرند و آماده دریافت تصمیم هستند.

### 2. Evaluate Conditions

از آخرین تغییرات، قبل از ثبت هر تصمیم، موتور با ConditionEvaluationService بررسی می‌کند که آیا pre-conditions مرتبط با approval definition یا approval step پاس شده‌اند یا خیر. این ارزیابی بر اساس یک context عمومی انجام می‌شود که شامل کاربر، actor، workflow، state، business entity و metadata است.

### 3. Approve

در این مرحله، موتور قبل از ثبت تصمیم، با سرویس Authorization بررسی می‌کند که کاربر مجاز است یا خیر. در صورت تأیید، تصمیم در قالب ApprovalDecision ثبت می‌شود، شمارنده‌های گام و نمونه به‌روزرسانی می‌شوند و در صورت نیاز گام بعدی فعال می‌شود.

### 4. Reject

اگر تصمیم reject صادر شود، گام و نمونه تأیید به وضعیت rejected تغییر می‌کنند و رویداد مربوطه منتشر می‌شود.

### 5. Return for Correction

در این حالت، گام و نمونه به وضعیت returned_for_correction تغییر می‌کنند و رویداد مخصوص آن منتشر می‌شود.

## لایه مجوز و تعیین approver

از آخرین تغییرات، موتور تأیید از یک لایه مجوز مستقل استفاده می‌کند که شامل موارد زیر است:

- بررسی احراز هویت کاربر
- بررسی فعال بودن نمونه تأیید
- بررسی فعال بودن گام فعلی
- بررسی واجد شرایط بودن کاربر برای آن گام
- جلوگیری از تصمیم تکراری برای یک approver در همان گام
- جلوگیری از self-approval با قانون‌های قابل‌پیکربندی
- بررسی suspension/banned بودن کاربر
- بررسی انقضای گام در صورت تنظیم expires_after_minutes

## استراتژی‌های تعیین approver

موتور از یک Registry برای حل approver استفاده می‌کند و در حال حاضر از سازوکارهای زیر پشتیبانی می‌کند:

- RoleApproverResolver: بر اساس required_role
- PermissionApproverResolver: بر اساس required_permission
- UserApproverResolver: بر اساس required_user_id
- DynamicApproverResolver: برای مسیرهای دینامیک و قابل توسعه

## یکپارچه‌سازی با Condition Engine

- Workflow transitions و Approval decisions از یک موتور شرط یکپارچه استفاده می‌کنند.
- در صورت شکست شرط، یک ConditionEvaluationException ایجاد می‌شود و هیچ تصمیمی ثبت نمی‌شود.
- این ساختار برای جلوگیری از hard-coded business rules و حفظ قابلیت استفاده در تبلیغات، KYC و سایر دامنه‌ها طراحی شده است.

## ویژگی‌های امنیتی و دوام

- تصمیم‌ها در تراکنش پایگاه‌داده ثبت می‌شوند
- از idempotency key برای جلوگیری از ثبت تصمیم‌های تکراری استفاده می‌شود
- قفل‌کردن رکوردهای نمونه و گام برای جلوگیری از race condition انجام می‌شود
- Policies و Exceptions برای کنترل دسترسی و خطاهای واضح در نظر گرفته شده‌اند

## حالت‌های پشتیبانی‌شده

- sequential
- any
- all
- quorum

## رویدادها

رویدادهای عمومی این لایه شامل موارد زیر هستند:

- ApprovalStarted
- ApprovalStepStarted
- ApprovalSubmitted
- ApprovalApproved
- ApprovalRejected
- ApprovalReturnedForCorrection
- ApprovalStepCompleted
- ApprovalCompleted

## تست‌ها

تست‌های مربوط به این بخش شامل موارد زیر است:

- چرخه زندگی core approval engine
- مجوز بر اساس role و permission
- محدودیت‌های specific user و self-approval
- جلوگیری از duplicate decision
- بلاک‌کردن کاربر suspend شده
- اعتبارسنجی delegation authorization
- ادغام با ConditionEngine در workflow و approval
