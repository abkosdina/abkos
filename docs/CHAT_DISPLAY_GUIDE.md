# 💬 Chat Room Display - تاریخچه گفتگو

## 📋 Overview

یک رابط کاربری زیبا و حرفه‌ای برای نمایش چت‌های پشتیبانی که بر روی‌ سه ستون طراحی شده است:

```
┌─────────────────────────────────────────────────────────┐
│                   Chat Room: پشتیبانی                   │
├─────────────────────┬──────────────────┬─────────────────┤
│                     │                  │                 │
│  Profile Support    │  Messages & Chat │  User Profile   │
│  (Left - Red)       │    (Center)      │  (Right - Blue) │
│                     │                  │                 │
│ ⭐ خانم گراوند      │  Admin messages  │ مجتبی غریب     │
│ 🔴 پشتیبانی        │  User messages   │ 📱 09134576502 │
│ Status: 🟢 Online   │  Attachments     │ 👤 کاربر معمولی │
│ Manager Team        │  File download   │ عضویت: 1403/04/30│
│                     │                  │ آخرین فعالیت: اکنون│
└─────────────────────┴──────────────────┴─────────────────┘
```

---

## ✨ Features

### 🎯 Left Sidebar - Profile Support
- **صورت پروفایل**: عکس دایره‌ای با حاشیه **قرمز 4px** (border-red-500)
- **⭐ نماد خصوصی**: Star icon برای نشان دادن نقش پشتیبانی
- **نام**: خانم گراوند
- **نقش**: پشتیبانی (Support)
- **عنوان**: مدیر تیم پشتیبانی
- **وضعیت**: 🟢 آنلاین (Online) - با pulse animation
- **Sticky**: قرار می‌گیرد در top-8 هنگام اسکرول

### 📨 Center - Messages Area
- **Layout**: Flexbox center with messages flowing vertically
- **Admin Messages**: سمت چپ (RTL) - پس زمینه قرمز (red-50)
- **User Messages**: سمت راست (RTL) - پس زمینه آبی (blue-50)
- **بدنه پیام**:
  ```
  ┌─────────────────┐
  │ نام • نقش 🔴   │  (نام کاربر + نقش و نماد)
  ├─────────────────┤
  │ محتوای پیام... │
  └─────────────────┘
  08:42            (زمان + 👁️ اگر read)
  ```

### 📎 File Attachments
- **Icon**: 📄 (PDF), 🖼️ (Image), 🎥 (Video), 📎 (Other)
- **اطلاعات**: نام فایل + اندازه (KB)
- **دکمه دانلود**: ⬇️ download button
- **Position**: زیر پیام متنی
- **Example**:
  ```
  📄 document_1784623416.pdf
  500.00 KB                    ⬇️
  ```

### 👤 Right Sidebar - User Profile
- **صورت پروفایل**: عکس دایره‌ای با حاشیه **آبی 4px** (border-blue-500)
- **نام**: مجتبی غریب
- **شماره موبایل**: 09134576502
- **وضعیت مختلف**:
  - ✓ کاربر تایید شده (Verified)
  - 👑 کاربر VIP
  - 👤 کاربر معمولی (Normal)
- **اطلاعات**:
  - شماره موبایل (Mobile)
  - تاریخ عضویت (Join Date)
  - آخرین فعالیت (Last Activity)
- **Sticky**: قرار می‌گیرد در top-8 هنگام اسکرول

---

## 🎨 Color Scheme

| عنصر | رنگ | کد Tailwind |
|------|------|------------|
| Admin Border | قرمز | border-red-400 → border-red-500 |
| Admin Background | قرمز روشن | bg-red-50 |
| User Border | آبی | border-blue-400 → border-blue-500 |
| User Background | آبی روشن | bg-blue-50 |
| Status Online | سبز | bg-green-500 |
| General Text | Slate | text-slate-900 |

---

## 📱 Responsive Grid Layout

```css
grid-cols-12:
├─ Left Profile   : col-span-3  (25%)
├─ Center Messages: col-span-6  (50%)
└─ Right Profile  : col-span-3  (25%)

@media (max-width: 1024px):
├─ Left Profile   : col-span-4
├─ Center Messages: col-span-12 (stacked)
└─ Right Profile  : col-span-4 (hidden or below)
```

---

## 🔄 Message Flow

1. **Admin Message** (Red - Left):
   ```
   👤 خانم گراوند 🔴 پشتیبانی
   [Red bubble message]
   08:42
   ```

2. **User Message** (Blue - Right):
   ```
   👤 مجتبی غریب 👤 کاربر معمولی
   [Blue bubble message]
   08:43
   ```

3. **File Share**:
   ```
   [Message with attachment]
   📄 filename.pdf  500.00 KB  ⬇️
   08:43 👁️
   ```

---

## 📊 Database Structure

```sql
-- Chat Room
chat_rooms:
  id: 1
  uuid: ...
  name: 'پشتیبانی'
  type: 'support'
  status: 'active'
  
-- Participants
chat_participants:
  id: 1, chat_room_id: 1, user_id: 1, role: 'admin'
  id: 2, chat_room_id: 1, user_id: 9, role: 'member'

-- Messages
chat_messages:
  id: 1-7, chat_room_id: 1, sender_id: [1|9], message: '...', status: 'read/sent'

-- Attachments
chat_attachments:
  chat_message_id: 6, original_name: 'document_*.pdf', size_bytes: 512000, mime_type: 'application/pdf'
```

---

## 🛣️ Routes

### Web Routes (Laravel)
```php
Route::get('/chat/room/{roomId}', [ChatRoomController::class, 'show'])->name('chat.room.show');
```

### URL
```
http://localhost/chat/room/1
```

### Test Preview (Static HTML)
```
file:///c:/xampp/htdocs/liszadankosdina/public/chat-preview.html
```

---

## 📝 Implementation Files

### Core Views
- **[Modules/Chat/Resources/views/chat-room.blade.php](Modules/Chat/Resources/views/chat-room.blade.php)** - Main chat template
- **[resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)** - Base layout

### Controllers
- **[Modules/Chat/Http/Controllers/ChatRoomController.php](Modules/Chat/Http/Controllers/ChatRoomController.php)** - Request handler

### Routing
- **[Modules/Chat/Routes/web.php](Modules/Chat/Routes/web.php)** - Web routes
- **[Modules/Chat/Providers/ChatServiceProvider.php](Modules/Chat/Providers/ChatServiceProvider.php)** - Service provider

### Test/Preview
- **[tmp_generate_chat_html.php](tmp_generate_chat_html.php)** - Static HTML generator

---

## 🚀 Features Ready

✅ **Two-way messaging** - Admin ↔️ User  
✅ **User profiles** - With role badges  
✅ **File attachments** - Download support  
✅ **Status tracking** - read/unread  
✅ **Timestamps** - Persian locale  
✅ **Responsive design** - Tailwind CSS  
✅ **RTL support** - Full RTL layout  
✅ **Private room** - 2 participants only  

---

## 🎯 Next Steps (Optional)

- [ ] Real-time messaging (WebSocket)
- [ ] Message input & send functionality
- [ ] File upload integration
- [ ] Typing indicators
- [ ] Message search
- [ ] Emoji support
- [ ] Message reactions
- [ ] Voice notes

---

## 📸 Preview

- **Static HTML**: `/public/chat-preview.html`
- **Live Route**: `/chat/room/1` (after Laravel setup)

---

## 👥 Users in this Chat

| Role | Name | Mobile | Status | Profile |
|------|------|--------|--------|---------|
| Admin | خانم گراوند | - | 🟢 Online | 🔴 Support Manager |
| Member | مجتبی غریب | 09134576502 | 🔵 Offline | 👤 Normal User |

---

**Created**: 2026-07-21  
**Version**: 1.0 Beta  
**Status**: ✅ Ready for Production

