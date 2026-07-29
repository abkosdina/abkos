# 🔄 Real-Time Chat System - Polling و Live Updates

## 📌 چگونه کار می‌کند؟

### مراحل:

1️⃣ **کاربر** صفحه چت رو باز می‌کند  
2️⃣ **Alpine.js** تابع `chatRoom()` رو initialize می‌کند  
3️⃣ **polling شروع می‌شود** - هر 2 ثانیه یک بار:
```
GET /api/chat/room/1/messages?since=2026-07-21T08:50:00Z
```

4️⃣ **Server** پیام‌های جدیدتر از تاریخ فرستاده شده رو برمی‌گرداند:
```json
{
  "messages": [
    {
      "id": 10,
      "sender_id": 1,
      "sender_name": "خانم گراوند",
      "message": "پیام جدید!",
      "created_at_fa": "08:45",
      "attachments": []
    }
  ],
  "last_message_time": "2026-07-21T08:45:00Z"
}
```

5️⃣ **JavaScript** پیام جدید رو:
   - ✅ تشخیص می‌دهد (duplicate check)
   - ✅ DOM سازی می‌کند
   - ✅ با animation اضافه می‌کند
   - ✅ scroll کالر رو پایین برده

6️⃣ **کاربر** ببیند: 💬 **پیام نمایش داده شد** - بدون رفرش صفحه!

---

## 🛠️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    BROWSER (Client)                      │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Alpine.js Component: chatRoom()                         │
│  ├─ init() → Start Polling                              │
│  ├─ checkNewMessages() → Fetch API                      │
│  ├─ addMessageToUI() → DOM Manipulation                 │
│  └─ sendMessage() → Send new messages                   │
│                                                          │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ HTTP REQUEST
                     │ GET /api/chat/room/1/messages?since=...
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                  SERVER (Laravel)                        │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ChatRoomController::getNewMessages()                   │
│  ├─ Parse ?since parameter                              │
│  ├─ Query: SELECT * FROM chat_messages                  │
│  │         WHERE chat_room_id = 1                       │
│  │         AND created_at > $since                      │
│  ├─ Include sender, attachments                         │
│  └─ Return JSON Response                                │
│                                                          │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ JSON RESPONSE
                     │ {messages: [...], last_message_time: "..."}
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              BROWSER AGAIN (Client-Side)                │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Alpine.js processes response                           │
│  ├─ For each new message:                               │
│  │  ├─ Check if already exists (data-message-id)        │
│  │  ├─ Create HTML bubble                               │
│  │  ├─ Insert to DOM                                    │
│  │  └─ Animate scroll                                   │
│  └─ Update lastMessageTime for next poll               │
│                                                          │
└─────────────────────────────────────────────────────────┘
                     │
                     │ REPEAT EVERY 2 SECONDS
                     │
                     ▼
              [Back to checkNewMessages()]
```

---

## 📝 کد کليدی

### 1️⃣ **Alpine.js Initialization** (chat-room.blade.php)

```html
<div x-data="chatRoom({{ $chatRoom->id }})">
    <p x-show="isPolling">🔄 تازه سازی خودکار...</p>
    ...
    <div id="messagesList"></div>
    ...
    <input id="messageInput" @keyup.enter="sendMessage($el.value)">
    <button @click="sendMessage(document.getElementById('messageInput').value)">
        ارسال
    </button>
</div>
```

### 2️⃣ **Polling Loop** (JavaScript)

```javascript
startPolling() {
    this.pollInterval = setInterval(() => {
        this.checkNewMessages();
    }, 2000);  // Every 2 seconds
}

async checkNewMessages() {
    const response = await fetch(
        `/api/chat/room/${this.roomId}/messages?since=${this.lastMessageTime}`
    );
    const data = await response.json();
    
    if (data.messages.length > 0) {
        data.messages.forEach(msg => this.addMessageToUI(msg));
        this.lastMessageTime = data.last_message_time;
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }
}
```

### 3️⃣ **API Controller** (ChatRoomController.php)

```php
public function getNewMessages($roomId)
{
    $since = request()->query('since');
    
    $query = ChatMessage::where('chat_room_id', $roomId)
        ->with('sender', 'attachments');
    
    if ($since) {
        $query->where('created_at', '>', $since);
    }
    
    $messages = $query->orderBy('created_at', 'asc')->get();
    
    return response()->json([
        'messages' => [...],
        'last_message_time' => $data->last()->created_at->format('...')
    ]);
}
```

---

## 🧪 تست کردن

### **روش 1**: استفاده از Terminal

```bash
# Terminal 1: صفحه چت رو باز کنید
# http://localhost/chat/room/1

# Terminal 2: پیام جدید بفرستید
cd c:\xampp\htdocs\liszadankosdina
php tmp_send_test_message.php
```

ببین! 👀 پیام جدید بدون رفرش نمایش داده می‌شود!

### **روش 2**: استفاده از API مستقیم

```bash
# Check for new messages
curl "http://localhost/api/chat/room/1/messages?since=2026-07-21T08:50:00Z"

# Response:
{
  "messages": [
    {
      "id": 10,
      "sender_id": 1,
      "sender_name": "خانم گراوند",
      "message": "سلام! این پیام جدید است...",
      "created_at_fa": "08:45"
    }
  ]
}
```

---

## ⚙️ Configuration

| تنظیم | مقدار | توضیح |
|-------|-------|--------|
| **Poll Interval** | 2000ms | هر 2 ثانیه چک کن |
| **Message ID Check** | data-message-id | prevent duplicates |
| **Auto Scroll** | On new message | scroll to bottom |
| **Status Indicator** | x-show="isPolling" | show/hide sync badge |

---

## 🚀 Performance

- ⚡ **Lightweight**: فقط تغییرات جدید دریافت می‌کند
- 💾 **Efficient**: SQL query با `WHERE created_at > $since` 
- 🔁 **Non-blocking**: polling در background اتفاق می‌افتد
- 📱 **Mobile-friendly**: کم bandwidth مصرف می‌کند

---

## 📦 فایل‌های استفاده شده

| فایل | نقش |
|------|------|
| **Modules/Chat/Resources/views/chat-room.blade.php** | Alpine.js + HTML Template |
| **Modules/Chat/Http/Controllers/ChatRoomController.php** | getNewMessages() API |
| **Modules/Chat/Routes/web.php** | Route: `/api/chat/room/{id}/messages` |
| **resources/views/layouts/app.blade.php** | Alpine.js CDN |
| **tmp_send_test_message.php** | Test Script |

---

## ✅ Features

✓ **Real-Time Updates** - بدون رفرش صفحه  
✓ **Polling** - هر 2 ثانیه  
✓ **Duplicate Prevention** - data-message-id check  
✓ **Auto Scroll** - پایین رفتن خودکار  
✓ **Status Indicator** - 🔄 نشانگر sync  
✓ **File Attachments** - support  
✓ **Message Status** - read/sent  
✓ **RTL Support** - تمام فارسی  

---

## 🔮 Next Steps

- [ ] Message send functionality
- [ ] WebSocket upgrade (from Polling)
- [ ] Typing indicators
- [ ] Message reactions
- [ ] Voice messages
- [ ] Video calls

---

**Created**: 2026-07-21  
**Version**: 1.0 Beta - Polling System  
**Status**: ✅ Ready for Testing

