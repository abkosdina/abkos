<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Modules\Chat\Models\ChatRoom;

// Get data
$chatRoom = ChatRoom::with('messages.sender', 'messages.attachments')
    ->where('name', 'پشتیبانی')
    ->first();

$messages = $chatRoom->messages()
    ->with('sender', 'attachments')
    ->orderBy('created_at', 'asc')
    ->get();

$admin = User::find(1);
$user = User::find(9);

// Generate HTML
$html = <<<'HTML'
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Room - پشتیبانی</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
        }
        .pulse-dot {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-50 to-slate-100" dir="rtl">
    <div class="min-h-screen py-8">
        <div class="container mx-auto px-4">
            <!-- Chat Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-900">پشتیبانی</h1>
                <p class="text-slate-600 mt-1">وضعیت: <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">فعال</span></p>
            </div>

            <!-- Main Chat Container -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Left Side: Admin Profile -->
                <div class="col-span-3">
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-red-400 sticky top-8">
                        <div class="text-center">
                            <!-- Profile Image -->
                            <div class="relative mx-auto w-24 h-24 mb-4">
                                <img 
                                    src="https://ui-avatars.com/api/?name=خانم+گراوند&color=dc2626&background=fca5a5"
                                    alt="خانم گراوند"
                                    class="w-24 h-24 rounded-full border-4 border-red-500 shadow-lg object-cover"
                                >
                                <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold" title="پشتیبانی">⭐</span>
                            </div>

                            <!-- Name and Role -->
                            <h3 class="text-lg font-bold text-slate-900">خانم گراوند</h3>
                            <p class="text-red-600 font-semibold text-sm mt-1">پشتیبانی</p>
                            
                            <!-- Role Badge -->
                            <div class="mt-4 px-3 py-1 bg-red-100 text-red-700 rounded-full inline-block text-xs font-semibold">
                                مدیر تیم پشتیبانی
                            </div>

                            <!-- Status -->
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                                    <span class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></span>
                                    آنلاین
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Center: Chat Messages -->
                <div class="col-span-6">
                    <div class="bg-white rounded-2xl shadow-lg p-8 max-h-[600px] overflow-y-auto">
                        <div class="space-y-6">
HTML;

foreach ($messages as $message) {
    $isAdmin = $message->sender_id === 1;
    $sender = $isAdmin ? $admin : $user;
    
    $profileUrl = "https://ui-avatars.com/api/?name=" . urlencode($sender->name) . "&color=" . ($isAdmin ? "dc2626" : "2563eb") . "&background=" . ($isAdmin ? "fca5a5" : "93c5fd");
    
    $senderLabel = $isAdmin ? '🔴 پشتیبانی' : '👤 کاربر معمولی';
    $flexDir = $isAdmin ? 'justify-start' : 'justify-end';
    $flexRow = $isAdmin ? 'flex-row' : 'flex-row-reverse';
    $borderColor = $isAdmin ? 'border-red-400' : 'border-blue-400';
    $textAlign = $isAdmin ? 'text-left' : 'text-right';
    $labelColor = $isAdmin ? 'text-red-600' : 'text-blue-600';
    $bgColor = $isAdmin ? 'red' : 'blue';
    
    $html .= <<<HTML
                            <div class="flex {$flexDir}">
                                <div class="flex gap-3 {$flexRow} max-w-xs">
                                    <!-- Sender Avatar -->
                                    <div class="flex-shrink-0">
                                        <img 
                                            src="{$profileUrl}"
                                            alt="{$sender->name}"
                                            class="w-8 h-8 rounded-full border-2 {$borderColor} object-cover"
                                            title="{$sender->name}"
                                        >
                                    </div>

                                    <!-- Message Bubble -->
                                    <div class="flex-1">
                                        <!-- Sender Name -->
                                        <p class="text-xs font-semibold text-slate-700 mb-1 {$textAlign}">
                                            {$sender->name}
                                            <span class="{$labelColor}">{$senderLabel}</span>
                                        </p>

                                        <!-- Message Content -->
                                        <div class="bg-{$bgColor}-50 border border-{$bgColor}-200 rounded-lg p-3">
                                            <p class="text-slate-900 text-sm" dir="rtl">{$message->message}</p>
                                        </div>
HTML;

    // Attachments
    if ($message->attachments->count() > 0) {
        foreach ($message->attachments as $attachment) {
            $icon = str_contains($attachment->mime_type, 'pdf') ? '📄' : 
                    (str_contains($attachment->mime_type, 'image') ? '🖼️' : 
                    (str_contains($attachment->mime_type, 'video') ? '🎥' : '📎'));
            
            $sizeKB = number_format($attachment->size_bytes / 1024, 2);
            
            $html .= <<<HTML
                                        <div class="mt-2">
                                            <div class="bg-slate-100 border border-slate-300 rounded-lg p-3 flex items-center gap-3">
                                                <div class="text-2xl">{$icon}</div>
                                                <div class="flex-1">
                                                    <p class="text-xs font-semibold text-slate-900">{$attachment->original_name}</p>
                                                    <p class="text-xs text-slate-600">{$sizeKB} KB</p>
                                                </div>
                                                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">⬇️</a>
                                            </div>
                                        </div>
HTML;
        }
    }

    $timestamp = $message->created_at->locale('fa')->format('H:i');
    $readBadge = $message->status === 'read' ? '👁️' : '';
    
    $html .= <<<HTML
                                        <!-- Timestamp -->
                                        <p class="text-xs text-slate-500 mt-1 {$textAlign}">
                                            {$timestamp} {$readBadge}
                                        </p>
                                    </div>
                                </div>
                            </div>
HTML;
}

$html .= <<<'HTML'
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="mt-6 bg-white rounded-xl shadow-lg p-4">
                        <div class="flex gap-3">
                            <input 
                                type="text"
                                placeholder="پیام خود را بنویسید..."
                                class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                dir="rtl"
                            >
                            <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                                ارسال
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Side: User Profile -->
                <div class="col-span-3">
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-blue-400 sticky top-8">
                        <div class="text-center">
                            <!-- Profile Image -->
                            <div class="relative mx-auto w-24 h-24 mb-4">
                                <img 
                                    src="https://ui-avatars.com/api/?name=مجتبی+غریب&color=2563eb&background=93c5fd"
                                    alt="مجتبی غریب"
                                    class="w-24 h-24 rounded-full border-4 border-blue-500 shadow-lg object-cover"
                                >
                            </div>

                            <!-- Name and Status -->
                            <h3 class="text-lg font-bold text-slate-900">مجتبی غریب</h3>
                            <p class="text-blue-600 font-semibold text-sm mt-1 ltr">09134576502</p>
                            
                            <!-- Status Badge -->
                            <div class="mt-4 px-3 py-1 bg-slate-100 text-slate-700 rounded-full inline-block text-xs font-semibold">
                                👤 کاربر معمولی
                            </div>

                            <!-- User Info -->
                            <div class="mt-6 space-y-3 pt-4 border-t border-slate-200 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">شماره موبایل:</span>
                                    <span class="font-semibold text-slate-900 ltr">09134576502</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">عضویت:</span>
                                    <span class="font-semibold text-slate-900">1403/04/30</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">آخرین فعالیت:</span>
                                    <span class="font-semibold text-slate-900">اکنون</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

// Save to file
file_put_contents(__DIR__ . '/public/chat-preview.html', $html);
echo "✅ Chat preview generated: /public/chat-preview.html\n";
echo "📺 Open: http://localhost/chat-preview.html\n";
