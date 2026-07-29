@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 py-8" x-data="chatRoom({{ $chatRoom->id }})">
    <div class="container mx-auto px-4">
        <!-- Chat Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-900">{{ $chatRoom->name }}</h1>
            <p class="text-slate-600 mt-1">
                وضعیت: <span class="badge badge-success">{{ $chatRoom->status }}</span>
                <span class="text-xs text-slate-500 mr-2" x-show="isPolling">🔄 تازه سازی خودکار...</span>
            </p>
        </div>

        <!-- Main Chat Container -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Side: Admin Profile -->
            <div class="lg:col-span-3 lg:sticky lg:top-8 h-fit">
                <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-red-400">
                    <div class="text-center">
                        <!-- Profile Image -->
                        <div class="relative mx-auto w-24 h-24 mb-4">
                            <img 
                                src="{{ $admin->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($admin->name).'&color=random&background=random' }}"
                                alt="{{ $admin->name }}"
                                class="w-24 h-24 rounded-full border-4 border-red-500 shadow-lg object-cover"
                            >
                            <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold" title="پشتیبانی">⭐</span>
                        </div>

                        <!-- Name and Role -->
                        <h3 class="text-lg font-bold text-slate-900">{{ $admin->name }}</h3>
                        <p class="text-red-600 font-semibold text-sm mt-1">پشتیبانی</p>
                        
                        <!-- Role Badge -->
                        <div class="mt-4 px-3 py-1 bg-red-100 text-red-700 rounded-full inline-block text-xs font-semibold">
                            مدیر تیم پشتیبانی
                        </div>

                        <!-- Status -->
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                آنلاین
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center: Chat Messages -->
            <div class="lg:col-span-6">
                <div id="messagesContainer" class="bg-white rounded-2xl shadow-lg p-6 max-h-[600px] overflow-y-auto">
                    <div class="space-y-4" id="messagesList">
                        @foreach($messages as $message)
                            @php
                                $isAdmin = $message->sender_id === 1;
                                $sender = $isAdmin ? $admin : $user;
                            @endphp

                            <div class="flex {{ $isAdmin ? 'justify-start' : 'justify-end' }}" data-message-id="{{ $message->id }}">
                                <div class="flex gap-3 {{ $isAdmin ? 'flex-row' : 'flex-row-reverse' }} max-w-xs">
                                    <!-- Sender Avatar -->
                                    <div class="flex-shrink-0">
                                        <img 
                                            src="{{ $sender->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($sender->name).'&color=random&background=random' }}"
                                            alt="{{ $sender->name }}"
                                            class="w-8 h-8 rounded-full border-2 {{ $isAdmin ? 'border-red-400' : 'border-blue-400' }} object-cover"
                                            title="{{ $sender->name }}"
                                        >
                                    </div>

                                    <!-- Message Bubble -->
                                    <div class="flex-1">
                                        <!-- Sender Name -->
                                        <p class="text-xs font-semibold text-slate-700 mb-1 {{ $isAdmin ? 'text-left' : 'text-right' }}">
                                            {{ $sender->name }}
                                            @if($isAdmin)
                                                <span class="text-red-600">🔴 پشتیبانی</span>
                                            @else
                                                @if($user->is_verified)
                                                    <span class="text-blue-600">✓ کاربر تایید شده</span>
                                                @elseif($user->is_vip)
                                                    <span class="text-amber-600">👑 کاربر VIP</span>
                                                @else
                                                    <span class="text-slate-600">👤 کاربر معمولی</span>
                                                @endif
                                            @endif
                                        </p>

                                        <!-- Message Content -->
                                        <div class="bg-{{ $isAdmin ? 'red' : 'blue' }}-50 border border-{{ $isAdmin ? 'red' : 'blue' }}-200 rounded-lg p-3">
                                            <p class="text-slate-900 text-sm" dir="rtl">{{ $message->message }}</p>
                                        </div>

                                        <!-- Attachments -->
                                        @if($message->attachments->count() > 0)
                                            <div class="mt-2 space-y-2">
                                                @foreach($message->attachments as $attachment)
                                                    <div class="bg-slate-100 border border-slate-300 rounded-lg p-3 flex items-center gap-3">
                                                        <div class="text-2xl">
                                                            @if(str_contains($attachment->mime_type, 'pdf'))
                                                                📄
                                                            @elseif(str_contains($attachment->mime_type, 'image'))
                                                                🖼️
                                                            @elseif(str_contains($attachment->mime_type, 'video'))
                                                                🎥
                                                            @else
                                                                📎
                                                            @endif
                                                        </div>
                                                        <div class="flex-1">
                                                            <p class="text-xs font-semibold text-slate-900">{{ $attachment->original_name }}</p>
                                                            <p class="text-xs text-slate-600">{{ number_format($attachment->size_bytes / 1024, 2) }} KB</p>
                                                        </div>
                                                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">⬇️</a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Timestamp -->
                                        <p class="text-xs text-slate-500 mt-1 {{ $isAdmin ? 'text-left' : 'text-right' }}">
                                            {{ $message->created_at->locale('fa')->format('H:i') }}
                                            @if($message->status === 'read')
                                                <span title="خوانده شده">👁️</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
                            id="messageInput"
                            @keyup.enter="sendMessage($el.value)"
                        >
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold" @click="sendMessage(document.getElementById('messageInput').value)">
                            ارسال
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: User Profile -->
            <div class="lg:col-span-3 lg:sticky lg:top-8 h-fit">
                <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-blue-400">
                    <div class="text-center">
                        <!-- Profile Image -->
                        <div class="relative mx-auto w-24 h-24 mb-4">
                            <img 
                                src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=random&background=random' }}"
                                alt="{{ $user->name }}"
                                class="w-24 h-24 rounded-full border-4 border-blue-500 shadow-lg object-cover"
                            >
                        </div>

                        <!-- Name and Status -->
                        <h3 class="text-lg font-bold text-slate-900">{{ $user->name }}</h3>
                        <p class="text-blue-600 font-semibold text-sm mt-1">{{ $user->mobile }}</p>
                        
                        <!-- Status Badge -->
                        @if($user->is_verified)
                            <div class="mt-4 px-3 py-1 bg-blue-100 text-blue-700 rounded-full inline-block text-xs font-semibold">
                                ✓ کاربر تایید شده
                            </div>
                        @elseif($user->is_vip)
                            <div class="mt-4 px-3 py-1 bg-amber-100 text-amber-700 rounded-full inline-block text-xs font-semibold">
                                👑 کاربر VIP
                            </div>
                        @else
                            <div class="mt-4 px-3 py-1 bg-slate-100 text-slate-700 rounded-full inline-block text-xs font-semibold">
                                👤 کاربر معمولی
                            </div>
                        @endif

                        <!-- User Info -->
                        <div class="mt-6 space-y-3 pt-4 border-t border-slate-200 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">شماره موبایل:</span>
                                <span class="font-semibold text-slate-900 ltr">{{ $user->mobile }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">عضویت:</span>
                                <span class="font-semibold text-slate-900">{{ $user->created_at->locale('fa')->format('d M Y') }}</span>
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

<style>
    .badge {
        @apply px-3 py-1 rounded-full text-xs font-semibold inline-block;
    }
    .badge-success {
        @apply bg-green-100 text-green-700;
    }
</style>

<script>
function chatRoom(roomId) {
    return {
        roomId: roomId,
        isPolling: true,
        lastMessageTime: new Date().toISOString(),
        pollInterval: null,

        init() {
            console.log('Chat room initialized for room:', this.roomId);
            this.startPolling();
            
            // Scroll to bottom
            this.$nextTick(() => {
                const container = document.getElementById('messagesContainer');
                container.scrollTop = container.scrollHeight;
            });
        },

        startPolling() {
            // Poll every 2 seconds for new messages
            this.pollInterval = setInterval(() => {
                this.checkNewMessages();
            }, 2000);
        },

        async checkNewMessages() {
            try {
                const response = await fetch(`/api/chat/room/${this.roomId}/messages?since=${this.lastMessageTime}`);
                const data = await response.json();

                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        this.addMessageToUI(message);
                    });

                    // Update last message time
                    this.lastMessageTime = data.last_message_time;

                    // Scroll to bottom
                    this.$nextTick(() => {
                        const container = document.getElementById('messagesContainer');
                        container.scrollTop = container.scrollHeight;
                    });
                }
            } catch (error) {
                console.error('Error checking new messages:', error);
            }
        },

        addMessageToUI(message) {
            const messagesList = document.getElementById('messagesList');
            
            // Check if message already exists
            if (document.querySelector(`[data-message-id="${message.id}"]`)) {
                return;
            }

            const isAdmin = message.sender_id === 1;
            const flexDir = isAdmin ? 'justify-start' : 'justify-end';
            const flexRow = isAdmin ? 'flex-row' : 'flex-row-reverse';
            const borderColor = isAdmin ? 'border-red-400' : 'border-blue-400';
            const bgColor = isAdmin ? 'red' : 'blue';
            const textAlign = isAdmin ? 'text-left' : 'text-right';
            const labelColor = isAdmin ? 'text-red-600' : 'text-blue-600';
            const roleLabel = isAdmin ? '🔴 پشتیبانی' : '👤 کاربر معمولی';

            let attachmentsHTML = '';
            if (message.attachments && message.attachments.length > 0) {
                message.attachments.forEach(att => {
                    const icon = att.mime_type.includes('pdf') ? '📄' :
                                 att.mime_type.includes('image') ? '🖼️' :
                                 att.mime_type.includes('video') ? '🎥' : '📎';
                    
                    attachmentsHTML += `
                        <div class="mt-2">
                            <div class="bg-slate-100 border border-slate-300 rounded-lg p-3 flex items-center gap-3">
                                <div class="text-2xl">${icon}</div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-slate-900">${att.name}</p>
                                    <p class="text-xs text-slate-600">${att.size} KB</p>
                                </div>
                                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">⬇️</a>
                            </div>
                        </div>
                    `;
                });
            }

            const messageHTML = `
                <div class="flex ${flexDir}" data-message-id="${message.id}">
                    <div class="flex gap-3 ${flexRow} max-w-xs">
                        <div class="flex-shrink-0">
                            <img 
                                src="${message.sender_photo}"
                                alt="${message.sender_name}"
                                class="w-8 h-8 rounded-full border-2 ${borderColor} object-cover"
                                title="${message.sender_name}"
                            >
                        </div>

                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-700 mb-1 ${textAlign}">
                                ${message.sender_name}
                                <span class="${labelColor}">${roleLabel}</span>
                            </p>

                            <div class="bg-${bgColor}-50 border border-${bgColor}-200 rounded-lg p-3">
                                <p class="text-slate-900 text-sm" dir="rtl">${message.message}</p>
                            </div>

                            ${attachmentsHTML}

                            <p class="text-xs text-slate-500 mt-1 ${textAlign}">
                                ${message.created_at_fa}
                                ${message.status === 'read' ? '<span title="خوانده شده">👁️</span>' : ''}
                            </p>
                        </div>
                    </div>
                </div>
            `;

            messagesList.insertAdjacentHTML('beforeend', messageHTML);
        },

        async sendMessage(text) {
            if (!text.trim()) return;

            const input = document.getElementById('messageInput');
            input.value = '';

            // TODO: Send message to backend
            console.log('Message to send:', text);
        },

        destroy() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        }
    };
}
</script>
@endsection
