<template x-if="activeGroupId === 'messages' && activeItem === 'گفتگوها'">
  <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
    <div class="bg-white border border-gray-100 rounded-2xl p-4 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold text-ink-900">گفتگوها</p>
          <p class="text-xs text-ink-400">همه گفتگوهای شما</p>
        </div>
        <button @click="fetchChatRooms()" class="text-[12px] text-teal-700 hover:text-teal-900">به‌روزرسانی</button>
      </div>

      <div x-show="loadingChatRooms" class="space-y-3">
        <template x-for="i in 5" :key="i"><div class="h-14 rounded-2xl skeleton"></div></template>
      </div>

      <div x-show="!loadingChatRooms && chatRooms.length === 0" class="rounded-2xl border border-dashed border-gray-200 p-6 text-center text-ink-500">
        هیچ گفتگویی پیدا نشد
      </div>

      <div x-show="!loadingChatRooms" class="space-y-3">
        <template x-for="room in chatRooms" :key="room.id">
          <button @click="selectChatRoom(room)" class="w-full text-right p-3 rounded-2xl border transition-colors hover:border-teal-200" :class="selectedChatRoom?.id === room.id ? 'border-teal-500 bg-teal-50' : 'border-gray-100 bg-white'">
            <p class="font-semibold text-ink-900 truncate" x-text="chatRoomTitle(room)"></p>
            <p class="text-xs text-ink-400 truncate" x-text="room.messages?.[0]?.message ?? 'بدون پیام'"></p>
            <p class="text-[11px] text-ink-300 mt-1" x-text="formatRoomDate(room.updated_at)"></p>
          </button>
        </template>
      </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-4 flex flex-col h-full">
      <div class="flex items-center justify-between mb-4">
        <div>
          <p class="text-sm font-semibold text-ink-900" x-text="selectedChatRoom ? chatRoomTitle(selectedChatRoom) : 'یک گفتگو انتخاب کنید'"></p>
          <p class="text-xs text-ink-400" x-text="selectedChatRoom ? 'پیام‌های این گفتگو' : 'برای مشاهده پیام‌ها یک گفتگوی سمت چپ را انتخاب کنید'"></p>
        </div>
        <div class="flex items-center gap-2">
          <button x-show="selectedChatRoom" @click="archiveSelectedRoom()" class="text-[12px] text-rose-600 hover:text-rose-800">بایگانی</button>
          <button x-show="selectedChatRoom" @click="markSelectedRoomRead()" class="text-[12px] text-sky-600 hover:text-sky-800">علامت خوانده‌شده</button>
        </div>
      </div>

      <div x-show="!selectedChatRoom" class="flex-1 rounded-2xl border border-gray-100 border-dashed p-8 text-center text-ink-500">
        گفتگو انتخاب نشده
      </div>

      <div x-show="selectedChatRoom" class="flex-1 flex flex-col">
        <div class="space-y-4 overflow-y-auto mb-4 pr-1" style="max-height:420px;" x-show="!loadingMessages">
          <template x-for="message in messages" :key="message.id">
            <div class="flex gap-3" :class="message.sender_id === currentUserId ? 'flex-row-reverse' : 'flex-row'">
              <!-- Profile Image -->
              <img :src="message.sender?.profile_photo_url || 'https://ui-avatars.com/api/?name=' + (message.sender?.name || 'User').replace(/ /g, '+') + '&background=random&color=random'" 
                   :alt="message.sender?.name || 'User'" 
                   class="w-9 h-9 rounded-full flex-shrink-0 object-cover border border-gray-200">
              
              <!-- Message Bubble with Sender Info -->
              <div class="flex-1 flex flex-col" :class="message.sender_id === currentUserId ? 'items-end' : 'items-start'">
                <!-- Sender Name & Status -->
                <div class="flex items-center gap-2 mb-1" :class="message.sender_id === currentUserId ? 'flex-row-reverse' : 'flex-row'">
                  <p class="text-xs font-semibold text-ink-900" x-text="message.sender?.name ?? 'کاربر'"></p>
                  <template x-if="message.sender?.is_verified">
                    <span class="text-[10px] text-sky-600 font-semibold">✓ تایید‌شده</span>
                  </template>
                  <template x-if="message.sender?.is_vip">
                    <span class="text-[10px] text-amber-600 font-semibold">👑 VIP</span>
                  </template>
                </div>
                
                <!-- Message Bubble -->
                <div class="rounded-2xl p-3 max-w-[calc(100%-80px)]" :class="message.sender_id === currentUserId ? 'bg-teal-50 text-right' : 'bg-gray-50 text-right'">
                  <p class="text-sm text-ink-900" x-text="message.message ?? 'پیام بدون متن'"></p>
                  <template x-if="message.attachments && message.attachments.length">
                    <div class="mt-3 space-y-2">
                      <template x-for="attachment in message.attachments" :key="attachment.id">
                        <a :href="attachment.file_url" target="_blank" class="block text-xs text-sky-600 underline" x-text="attachment.file_url ? attachment.file_path.split('/').pop() : 'مشاهده فایل'"></a>
                      </template>
                    </div>
                  </template>
                </div>
                
                <!-- Timestamp -->
                <p class="text-[11px] text-ink-400 mt-1" x-text="formatRoomDate(message.created_at)"></p>
              </div>
            </div>
          </template>
        </div>
        <div x-show="loadingMessages" class="flex-1 space-y-3">
          <template x-for="i in 4" :key="i"><div class="h-14 rounded-2xl skeleton"></div></template>
        </div>
        <div class="mt-auto">
          <textarea x-model="messageDraft" rows="3" class="w-full rounded-2xl border border-gray-200 p-3 text-right text-ink-900" placeholder="پیام خود را بنویسید..."></textarea>
          <div class="mt-3 flex flex-col gap-2">
            <label class="block text-[12px] text-ink-500">ضمیمه فایل</label>
            <input type="file" @change="handleAttachmentChange($event)" class="w-full text-sm text-ink-700" />
            <p x-show="attachmentFile" class="text-[12px] text-ink-500">
              فایل انتخاب‌شده: <span x-text="attachmentFile?.name ?? ''"></span>
            </p>
          </div>
          <div class="flex items-center justify-between gap-2 mt-3">
            <button @click="sendChatMessage()" class="inline-flex items-center justify-center rounded-2xl bg-teal-600 text-white px-4 py-2 text-[13px] font-semibold hover:bg-teal-700">ارسال پیام</button>
            <span class="text-[11px] text-ink-400" x-show="selectedChatRoom" x-text="messages.length + ' پیام'"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<template x-if="activeGroupId === 'messages' && activeItem === 'آرشیو پیام‌ها'">
  <div class="bg-white border border-gray-100 rounded-2xl p-4">
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-sm font-semibold text-ink-900">آرشیو گفتگوها</p>
        <p class="text-xs text-ink-400">گفتگوهای بایگانی شده</p>
      </div>
      <button @click="fetchArchivedRooms()" class="text-[12px] text-teal-700 hover:text-teal-900">بارگذاری مجدد</button>
    </div>

    <div x-show="loadingChatRooms" class="space-y-3">
      <template x-for="i in 5" :key="i"><div class="h-14 rounded-2xl skeleton"></div></template>
    </div>

    <div x-show="!loadingChatRooms && archivedRooms.length === 0" class="rounded-2xl border border-dashed border-gray-200 p-6 text-center text-ink-500">
      هیچ گفتگویی در آرشیو وجود ندارد
    </div>

    <div x-show="!loadingChatRooms" class="space-y-3">
      <template x-for="room in archivedRooms" :key="room.id">
        <button @click="selectChatRoom(room)" class="w-full text-right p-3 rounded-2xl border transition-colors hover:border-teal-200" :class="selectedChatRoom?.id === room.id ? 'border-teal-500 bg-teal-50' : 'border-gray-100 bg-white'">
          <p class="font-semibold text-ink-900 truncate" x-text="chatRoomTitle(room)"></p>
          <p class="text-xs text-ink-400 truncate" x-text="room.messages?.[0]?.message ?? 'بدون پیام'"></p>
          <p class="text-[11px] text-ink-300 mt-1" x-text="formatRoomDate(room.updated_at)"></p>
        </button>
      </template>
    </div>
  </div>
</template>

<template x-if="activeGroupId === 'messages' && activeItem === 'فایل‌های ارسالی'">
  <div class="bg-white border border-gray-100 rounded-2xl p-10 text-center">
    <p class="font-bold text-[15px] text-ink-900">فایل‌های ارسالی</p>
    <p class="text-[13px] text-ink-400 mt-2">این بخش به‌زودی با پشتیبانی از ضمیمه پیام فعال خواهد شد.</p>
  </div>
</template>

<template x-if="activeGroupId === 'users'">
  <div class="space-y-4 animate-fadeUp">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <p class="text-sm font-semibold text-ink-900">مدیریت کاربران</p>
        <p class="text-xs text-ink-400">نمایش کاربران با جستجو، صفحه‌بندی و عملیات CRUD ساده</p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative w-full sm:w-72">
          <input
            type="text"
            x-model="userSearch"
            value=""
            autocomplete="off"
            @input="debounceFetchUsers()"
            placeholder="جستجو بر اساس نام، ایمیل یا موبایل"
            class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-right text-sm shadow-sm outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-200"
          />
        </div>
        <button
          @click="openCreateUser()"
          class="inline-flex items-center justify-center rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
        >
          ایجاد کاربر جدید
        </button>
      </div>

    </div>

    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
      <table class="min-w-full text-right">
        <thead class="bg-gray-50 text-[13px] uppercase tracking-wide text-ink-500">
          <tr>
            <th class="px-4 py-3 text-right">نام</th>
            <th class="px-4 py-3 text-right">ایمیل</th>
            <th class="px-4 py-3 text-right">موبایل</th>
            <th class="px-4 py-3 text-right">نقش‌ها</th>
            <th class="px-4 py-3 text-right">وضعیت</th>
            <th class="px-4 py-3 text-right">تاریخ ثبت</th>
            <th class="px-4 py-3 text-right">عملیات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <template x-if="usersLoading">
            <tr>
              <td colspan="7" class="px-4 py-10 text-center text-sm text-ink-500">
                <div class="flex items-center justify-center gap-3">
                  <div class="h-4 w-24 rounded-full bg-gray-200 animate-pulse"></div>
                  در حال بارگذاری کاربران...
                </div>
              </td>
            </tr>
          </template>
          <template x-if="!usersLoading && users.length === 0">
            <tr>
              <td colspan="7" class="px-4 py-10 text-center text-sm text-ink-500">هیچ کاربری پیدا نشد.</td>
            </tr>
          </template>
          <template x-for="user in users" :key="user.id">
            <tr class="transition hover:bg-gray-50">
              <td class="px-4 py-4 text-sm font-medium text-ink-900" x-text="user.name"></td>
              <td class="px-4 py-4 text-sm text-ink-700" x-text="user.email || 'ندارد'"></td>
              <td class="px-4 py-4 text-sm text-ink-700" x-text="user.mobile || 'ندارد'"></td>
              <td class="px-4 py-4 text-sm text-ink-700">
                <template x-for="role in user.role_labels" :key="role">
                  <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700 mr-1" x-text="role"></span>
                </template>
              </td>
              <td class="px-4 py-4 text-sm">
                <span
                  class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold"
                  :class="user.is_suspended ? 'bg-rose-100 text-rose-700' : (user.status === 'inactive' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')"
                  x-text="user.is_suspended ? 'معلق' : (user.status === 'inactive' ? 'غیر فعال' : 'فعال')"
                ></span>
              </td>
              <td class="px-4 py-4 text-sm text-ink-500" x-text="new Date(user.created_at).toLocaleDateString('fa-IR')"></td>
              <td class="px-4 py-4 text-sm text-ink-700">
                <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                  <button
                    @click="openUserProfile(user)"
                    class="rounded-2xl bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 transition hover:bg-sky-200"
                  >پروفایل</button>
                  <button
                    @click="toggleUserSuspension(user)"
                    class="rounded-2xl bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-200"
                  x-text="user.is_suspended ? 'رفع تعلیق' : 'تعلیق'"
                  ></button>
                  <button
                    @click="openEditUser(user)"
                    class="rounded-2xl bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
                  >ویرایش</button>
                  <button
                    @click="deleteUser(user)"
                    class="rounded-2xl bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-200"
                  >حذف</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="flex flex-col gap-3 rounded-3xl border border-gray-200 bg-white p-4 text-sm text-ink-500 sm:flex-row sm:items-center sm:justify-between">
      <div>
        نمایش <span class="font-semibold text-ink-900" x-text="users.length"></span> از <span class="font-semibold text-ink-900" x-text="usersTotal"></span> کاربر
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="setUserPage(userPage - 1)"
          :disabled="userPage <= 1"
          class="rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 hover:border-teal-400 hover:text-teal-700"
        >
          قبلی
        </button>
        <span>صفحه <span class="font-semibold text-ink-900" x-text="userPage"></span> از <span class="font-semibold text-ink-900" x-text="userLastPage"></span></span>
        <button
          @click="setUserPage(userPage + 1)"
          :disabled="userPage >= userLastPage"
          class="rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 hover:border-teal-400 hover:text-teal-700"
        >
          بعدی
        </button>
      </div>
    </div>

    <div x-show="userFormOpen" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/30 p-4 pt-10">
      <div class="w-full max-w-4xl overflow-hidden rounded-[28px] bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <div>
            <p class="text-base font-semibold text-ink-900" x-text="userFormMode === 'create' ? 'ثبت کاربر جدید' : 'ویرایش کاربر'"></p>
            <p class="text-sm text-ink-500">اطلاعات کاربر را در تب‌های جداگانه مدیریت کنید.</p>
          </div>
          <button @click="closeUserForm()" class="text-sm font-semibold text-ink-500 transition hover:text-ink-900">بستن</button>
        </div>
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
          <div class="flex flex-wrap gap-2">
            <button @click="userFormTab = 'general'" :class="userFormTab === 'general' ? 'bg-white text-teal-700 shadow-sm' : 'bg-transparent text-ink-600'" class="rounded-full border border-transparent px-4 py-2 text-sm font-semibold transition">اطلاعات پایه</button>
            <button @click="userFormTab = 'security'" :class="userFormTab === 'security' ? 'bg-white text-teal-700 shadow-sm' : 'bg-transparent text-ink-600'" class="rounded-full border border-transparent px-4 py-2 text-sm font-semibold transition">تغییر رمز</button>
            <button @click="userFormTab = 'roles'" :class="userFormTab === 'roles' ? 'bg-white text-teal-700 shadow-sm' : 'bg-transparent text-ink-600'" class="rounded-full border border-transparent px-4 py-2 text-sm font-semibold transition">نقش‌ها و دسترسی‌ها</button>
            <button @click="userFormTab = 'sidebar'" :class="userFormTab === 'sidebar' ? 'bg-white text-teal-700 shadow-sm' : 'bg-transparent text-ink-600'" class="rounded-full border border-transparent px-4 py-2 text-sm font-semibold transition">منوی سایدبار</button>
          </div>
        </div>

        <div class="space-y-6 px-6 py-5">
          <div x-show="userFormTab === 'general'" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm text-ink-700">
                <span>نام</span>
                <input x-model="userForm.name" type="text" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200" />
              </label>
              <label class="space-y-2 text-sm text-ink-700">
                <span>ایمیل</span>
                <input x-model="userForm.email" type="email" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200" />
              </label>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm text-ink-700">
                <span>موبایل</span>
                <input x-model="userForm.mobile" type="text" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200" />
              </label>
              <label class="space-y-2 text-sm text-ink-700">
                <span>وضعیت</span>
                <select x-model="userForm.status" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200">
                  <option value="active">فعال</option>
                  <option value="inactive">غیر فعال</option>
                </select>
              </label>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
              <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-ink-700">
                <input type="checkbox" x-model="userForm.is_verified" class="h-4 w-4 rounded text-teal-600" />
                تایید شده
              </label>
              <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-ink-700">
                <input type="checkbox" x-model="userForm.is_suspended" class="h-4 w-4 rounded text-teal-600" />
                معلق
              </label>
            </div>
            <label class="space-y-2 text-sm text-ink-700">
              <span>یادداشت بررسی</span>
              <textarea x-model="userForm.moderation_note" rows="3" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200"></textarea>
            </label>
          </div>

          <div x-show="userFormTab === 'security'" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm text-ink-700">
                <span>رمز عبور</span>
                <input x-model="userForm.password" type="password" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200" placeholder="فقط برای تغییر وارد کنید" />
              </label>
              <label class="space-y-2 text-sm text-ink-700">
                <span>تکرار رمز عبور</span>
                <input x-model="userForm.password_confirmation" type="password" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200" placeholder="اگر رمز وارد شد تکرار شود" />
              </label>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4 text-sm text-ink-700">
              <p class="font-semibold text-ink-900">نکته</p>
              <p class="mt-2 text-sm text-ink-600">اگر نمی‌خواهید رمز را تغییر دهید، فیلدها را خالی بگذارید.</p>
            </div>
          </div>

          <div x-show="userFormTab === 'roles'" class="space-y-4">
            <div class="grid gap-4 lg:grid-cols-2">
              <div class="rounded-3xl border border-gray-200 bg-white p-4">
                <p class="text-sm font-semibold text-ink-900">نقش‌ها</p>
                <div class="mt-3 grid gap-2">
                  <template x-if="availableRoles.length === 0">
                    <p class="text-xs text-ink-500">بارگذاری نقش‌ها...</p>
                  </template>
                  <template x-for="role in availableRoles" :key="role.name">
                    <label class="inline-flex items-center gap-2 rounded-2xl border border-gray-100 bg-slate-50 px-3 py-2 text-sm text-ink-700">
                      <input type="checkbox" :value="role.name" x-model="selectedRoles" class="h-4 w-4 rounded text-teal-600" />
                      <span x-text="role.slug_fa || role.display_name || role.name"></span>
                    </label>
                  </template>
                </div>
              </div>
              <div class="rounded-3xl border border-gray-200 bg-white p-4">
                <p class="text-sm font-semibold text-ink-900">دسترسی‌ها</p>
                <div class="mt-3 grid max-h-[320px] gap-2 overflow-y-auto">
                  <template x-if="availablePermissions.length === 0">
                    <p class="text-xs text-ink-500">بارگذاری دسترسی‌ها...</p>
                  </template>
                  <template x-for="permission in availablePermissions" :key="permission.name">
                    <label class="inline-flex items-center gap-2 rounded-2xl border border-gray-100 bg-slate-50 px-3 py-2 text-sm text-ink-700">
                      <input type="checkbox" :value="permission.name" x-model="selectedPermissions" class="h-4 w-4 rounded text-teal-600" />
                      <span x-text="permission.slug_fa || permission.display_name || permission.name"></span>
                    </label>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <div x-show="userFormTab === 'sidebar'" class="space-y-4">
            <div class="grid gap-4 lg:grid-cols-2">
              <div class="rounded-3xl border border-gray-200 bg-white p-4">
                <p class="text-sm font-semibold text-ink-900">انتخاب نقش برای سایدبار</p>
                <select x-model="sidebarPreviewRole" class="mt-3 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-right text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-200">
                  <option value="">انتخاب کنید...</option>
                  <template x-for="role in availableRoles" :key="role.name">
                    <option :value="role.name" x-text="role.slug_fa || role.display_name || role.name"></option>
                  </template>
                </select>
                <p class="mt-3 text-xs text-ink-500">با انتخاب نقش، می‌توانید آیتم‌های نمایش داده شده در سایدبار را مدیریت کنید.</p>
              </div>
              <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4 text-sm text-ink-700">
                <p class="font-semibold text-ink-900">آخرین انتخاب</p>
                <p class="mt-2 text-sm text-ink-600" x-text="sidebarPreviewRole ? 'نقش انتخاب شده: ' + sidebarPreviewRole : 'نقشی انتخاب نشده است.'"></p>
              </div>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-4">
              <template x-if="!sidebarPreviewRole">
                <p class="text-sm text-ink-500">برای ویرایش منو باید ابتدا یک نقش انتخاب شود.</p>
              </template>
              <template x-if="sidebarPreviewRole && getRoleMenuEditing().length === 0">
                <p class="text-sm text-ink-500">برای این نقش تنظیماتی وجود ندارد.</p>
              </template>
              <template x-for="group in getRoleMenuEditing()" :key="group.id">
                <div class="rounded-3xl border border-gray-100 p-4">
                  <div class="flex items-center justify-between gap-2">
                    <label class="inline-flex items-center gap-2 text-sm text-ink-700">
                      <input type="checkbox" :checked="group.visible" @change="toggleSidebarGroupForSelectedRole(group.id)" class="h-4 w-4 rounded text-teal-600" />
                      <span class="font-semibold" x-text="group.label"></span>
                    </label>
                  </div>
                  <div class="mt-3 grid gap-2">
                    <template x-for="item in group.items" :key="item.name">
                      <label class="inline-flex items-center gap-2 rounded-2xl border border-gray-100 bg-slate-50 px-3 py-2 text-xs text-ink-700">
                        <input type="checkbox" :checked="item.visible" @change="toggleSidebarItemForSelectedRole(group.id, item.name)" class="h-3.5 w-3.5 rounded text-teal-600" />
                        <span x-text="item.name"></span>
                      </label>
                    </template>
                  </div>
                </div>
              </template>
              <button x-show="sidebarPreviewRole" @click="saveSidebarMenuConfigForSelectedRole()" class="mt-3 inline-flex items-center justify-center rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700">ذخیره تنظیمات منو</button>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:justify-end">
          <button @click="closeUserForm()" class="rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-gray-50">انصراف</button>
          <button @click="saveUser()" class="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700">ذخیره</button>
        </div>
      </div>
    </div>

    <div x-show="userProfileOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
      <div class="w-full max-w-3xl overflow-hidden rounded-[28px] bg-white shadow-2xl">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-base font-semibold text-ink-900" x-text="userProfileDetails ? userProfileDetails.name : 'پروفایل کاربر'"></p>
            <p class="text-sm text-ink-500">اطلاعات کاربری، فعالیت‌ها و تراکنش‌ها</p>
          </div>
          <button @click="closeUserProfile()" class="text-sm font-semibold text-ink-500 transition hover:text-ink-900">بستن</button>
        </div>
        <div class="px-6 py-5">
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-2xl bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700" x-text="userProfileDetails?.status === 'active' ? 'فعال' : (userProfileDetails?.status === 'suspended' ? 'معلق' : userProfileDetails?.status)"></span>
              <span class="rounded-2xl bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700" x-text="userProfileDetails?.roles?.join('، ') || 'بدون نقش'"></span>
            </div>
            <div class="flex flex-wrap gap-2">
              <button @click="userProfileTab = 'details'" :class="userProfileTab === 'details' ? 'bg-teal-600 text-white' : 'bg-gray-50 text-ink-700'" class="rounded-2xl px-4 py-2 text-xs font-semibold transition">جزئیات</button>
              <button @click="userProfileTab = 'activity'" :class="userProfileTab === 'activity' ? 'bg-teal-600 text-white' : 'bg-gray-50 text-ink-700'" class="rounded-2xl px-4 py-2 text-xs font-semibold transition">فعالیت‌ها</button>
              <button @click="userProfileTab = 'wallet'" :class="userProfileTab === 'wallet' ? 'bg-teal-600 text-white' : 'bg-gray-50 text-ink-700'" class="rounded-2xl px-4 py-2 text-xs font-semibold transition">کیف پول</button>
              <button @click="userProfileTab = 'permissions'" :class="userProfileTab === 'permissions' ? 'bg-teal-600 text-white' : 'bg-gray-50 text-ink-700'" class="rounded-2xl px-4 py-2 text-xs font-semibold transition">دسترسی‌ها</button>
            </div>
          </div>

          <div x-show="userProfileLoading" class="rounded-3xl border border-gray-200 bg-gray-50 p-8 text-center text-sm text-ink-500">
            در حال بارگذاری اطلاعات کاربر...
          </div>

          <div x-show="!userProfileLoading">
            <div x-show="userProfileTab === 'details'" class="space-y-4">
              <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4">
                  <p class="text-sm font-semibold text-ink-900">اطلاعات پایه</p>
                  <div class="mt-3 space-y-2 text-sm text-ink-700">
                    <p><span class="font-semibold">نام:</span> <span x-text="userProfileDetails?.name || '-' "></span></p>
                    <p><span class="font-semibold">ایمیل:</span> <span x-text="userProfileDetails?.email || '-' "></span></p>
                    <p><span class="font-semibold">موبایل:</span> <span x-text="userProfileDetails?.mobile || '-' "></span></p>
                    <p><span class="font-semibold">وضعیت تایید:</span> <span x-text="userProfileDetails?.is_verified ? 'تایید شده' : 'تایید نشده'"></span></p>
                    <p><span class="font-semibold">معوقیت:</span> <span x-text="userProfileDetails?.is_suspended ? 'بله' : 'خیر'"></span></p>
                  </div>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4">
                  <p class="text-sm font-semibold text-ink-900">پروفایل تکمیلی</p>
                  <div class="mt-3 space-y-2 text-sm text-ink-700">
                    <p><span class="font-semibold">کدملی:</span> <span x-text="userProfileDetails?.profile?.national_code || '-' "></span></p>
                    <p><span class="font-semibold">تاریخ تولد:</span> <span x-text="userProfileDetails?.profile?.birth_date || '-' "></span></p>
                    <p><span class="font-semibold">استان / شهر:</span> <span x-text="((userProfileDetails?.profile?.province || '') + ' / ' + (userProfileDetails?.profile?.city || '')).trim() || '-' "></span></p>
                    <p><span class="font-semibold">آدرس:</span> <span x-text="userProfileDetails?.profile?.address || '-' "></span></p>
                    <p><span class="font-semibold">وضعیت حساب:</span> <span x-text="userProfileDetails?.profile?.status || '-' "></span></p>
                  </div>
                </div>
              </div>
              <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-ink-900">یادداشت بررسی</p>
                <p class="mt-3 text-sm text-ink-700" x-text="userProfileDetails?.moderation_note || 'یادداشتی ثبت نشده است.'"></p>
              </div>
            </div>

            <div x-show="userProfileTab === 'activity'" class="space-y-4">
              <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-ink-900">آخرین فعالیت‌ها</p>
                <template x-if="userProfileActivity.length === 0">
                  <p class="mt-3 text-sm text-ink-500">فعالی ثبت شده‌ای وجود ندارد.</p>
                </template>
                <template x-for="activity in userProfileActivity" :key="activity.id">
                  <div class="mt-3 rounded-2xl border border-gray-200 bg-white p-4 text-sm text-ink-700">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <p class="font-semibold" x-text="activity.event"></p>
                      <p class="text-xs text-ink-400" x-text="new Date(activity.created_at).toLocaleString('fa-IR')"></p>
                    </div>
                    <p class="mt-2 text-xs text-ink-500">موضوع: <span x-text="activity.subject_type || '-' "></span></p>
                    <pre class="mt-2 overflow-x-auto rounded-2xl bg-gray-50 p-3 text-xs text-ink-600" x-text="JSON.stringify(activity.properties || {}, null, 2)"></pre>
                  </div>
                </template>
              </div>
            </div>

            <div x-show="userProfileTab === 'wallet'" class="space-y-4">
              <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4 text-sm text-ink-700">
                <p class="font-semibold text-ink-900">کیف پول کاربر</p>
                <template x-if="!userProfileWallet">
                  <p class="mt-3 text-sm text-ink-500">کیف پول ثبت‌شده‌ای برای این کاربر وجود ندارد.</p>
                </template>
                <template x-if="userProfileWallet">
                  <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl bg-white p-4">
                      <p class="text-xs text-ink-500">شناسه کیف پول</p>
                      <p class="mt-2 font-semibold text-ink-900" x-text="userProfileWallet.id"></p>
                    </div>
                    <div class="rounded-2xl bg-white p-4">
                      <p class="text-xs text-ink-500">موجودی</p>
                      <p class="mt-2 font-semibold text-ink-900" x-text="userProfileWallet.balance?.amount ? userProfileWallet.balance.amount + ' تومان' : '0 تومان'"></p>
                    </div>
                  </div>
                  <div class="mt-4 space-y-3">
                    <template x-for="transaction in userProfileWallet.transactions" :key="transaction.id">
                      <div class="rounded-2xl border border-gray-200 bg-white p-4 text-sm text-ink-700">
                        <div class="flex items-center justify-between gap-2">
                          <p class="font-semibold" x-text="transaction.type"></p>
                          <p class="text-xs text-ink-400" x-text="new Date(transaction.created_at).toLocaleString('fa-IR')"></p>
                        </div>
                        <p class="mt-2 text-sm">مبلغ: <span x-text="transaction.amount"></span></p>
                        <p class="text-sm">توضیحات: <span x-text="transaction.description || '-' "></span></p>
                      </div>
                    </template>
                  </div>
                </template>
              </div>
            </div>

            <div x-show="userProfileTab === 'permissions'" class="space-y-4">
              <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-ink-900">دسترسی‌ها</p>
                <template x-if="!userProfileDetails?.permissions?.length">
                  <p class="mt-3 text-sm text-ink-500">دسترسی‌ای برای این کاربر تعیین نشده است.</p>
                </template>
                <div class="mt-3 flex flex-wrap gap-2">
                  <template x-for="permission in userProfileDetails?.permissions || []" :key="permission">
                    <span class="rounded-2xl bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700" x-text="permission"></span>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<template x-if="activeGroupId !== 'messages' && activeGroupId !== 'users'">
  <div class="animate-fadeUp">
    <div class="bg-white border border-dashed border-gray-200 rounded-2xl p-10 text-center">
      <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mx-auto mb-4 text-[22px]" x-text="activeGroupIcon"></div>
      <p class="font-extrabold text-[16px] text-ink-900" x-text="activeItem"></p>
      <p class="text-[13px] text-ink-400 mt-2 max-w-sm mx-auto">
        این بخش آماده اتصال به API واقعی است. محتوای «<span x-text="activeItem"></span>» از زیرمجموعه «<span x-text="activeGroupLabel"></span>» اینجا نمایش داده می‌شود.
      </p>
    </div>
  </div>
</template>
