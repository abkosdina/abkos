<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد کاربری | مستر وام</title>

    <link rel="stylesheet" href="{{ asset('cdn/Vazirmatn-font-face.css') }}">
    <link rel="stylesheet" href="{{ asset('cdn/toastify.min.css') }}">
    <script src="{{ asset('cdn/toastify.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        [x-cloak] { display: none !important; }
        .stagger > * { animation: fadeUp .55s cubic-bezier(.22,1,.36,1) both; }
        .stagger > *:nth-child(1){animation-delay:.02s} .stagger > *:nth-child(2){animation-delay:.06s}
        .stagger > *:nth-child(3){animation-delay:.10s} .stagger > *:nth-child(4){animation-delay:.14s}
        .stagger > *:nth-child(5){animation-delay:.18s} .stagger > *:nth-child(6){animation-delay:.22s}
        .card-hover { transition: transform .3s cubic-bezier(.22,1,.36,1), box-shadow .3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 16px 32px -16px rgba(15,156,140,.25); }
        .skeleton { background: linear-gradient(90deg,#eef4f3 25%,#f7fbfa 37%,#eef4f3 63%); background-size:400px 100%; animation: shimmer 1.4s infinite linear; }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #d7ece8; border-radius: 8px; }
        ::-webkit-scrollbar { width: 7px; }
        ::-webkit-scrollbar-thumb { background: #cdeee8; border-radius: 8px; }
    </style>
</head>

<body class="bg-gray-50/60 text-ink-900 antialiased" x-data="dashboardApp()" x-init="init()">

<div class="flex h-screen overflow-hidden">
    @include('components.ads.dashboard.sidebar')

    <div class="flex-1 flex flex-col min-w-0">
        @include('components.ads.dashboard.topbar')

        <main class="flex-1 overflow-y-auto p-5 sm:p-7">
            @include('components.ads.dashboard.overview')
            @include('components.ads.dashboard.placeholder')
        </main>
    </div>
</div>

<script>
function dashboardApp() {
  const toPrettyJson = (value) => JSON.stringify(value ?? [], null, 2);
  const extractResponseData = (resp, fallback = null) => {
    if (resp && typeof resp === 'object' && Object.prototype.hasOwnProperty.call(resp, 'data')) {
      return resp.data ?? fallback;
    }
    return resp ?? fallback;
  };
  const apiRequest = async (method, url, payload = null, fallback = null, config = {}) => {
    const response = await axios({ method, url, data: payload, ...config });
    return response.data ?? fallback;
  };
  const apiGetRaw = async (url, fallback = null, config = {}) => apiRequest('get', url, null, fallback, config);
  const apiGet = async (url, fallback = null, config = {}) => {
    const data = await apiGetRaw(url, null, config);
    return extractResponseData(data, fallback);
  };
  const apiPostRaw = async (url, payload = {}, fallback = null, config = {}) => apiRequest('post', url, payload, fallback, config);
  const apiPost = async (url, payload = {}, fallback = null, config = {}) => {
    const data = await apiPostRaw(url, payload, null, config);
    return extractResponseData(data, fallback);
  };
  const apiPut = async (url, payload = {}, fallback = null, config = {}) => {
    const data = await apiRequest('put', url, payload, fallback, config);
    return extractResponseData(data, fallback);
  };
  const apiDelete = async (url, fallback = null, config = {}) => {
    const data = await apiRequest('delete', url, null, fallback, config);
    return extractResponseData(data, fallback);
  };
  const apiPostForm = async (url, payload, fallback = null, config = {}) => {
    const data = await apiPostRaw(url, payload, fallback, config);
    return extractResponseData(data, fallback);
  };
  const normalizeRoleNameHelper = (role) => String(role || '').trim().toLowerCase().replace(/[_\s]+/g, '-');
  const canonicalRoleKey = (roleKey) => {
    const normalized = normalizeRoleNameHelper(roleKey);
    if (!normalized) return null;
    if (['super-admin', 'super admin'].includes(normalized)) return 'super-admin';
    if (['administrator', 'admin'].includes(normalized)) return 'admin';
    if (['bank-employee', 'bank_employee'].includes(normalized)) return 'bank-employee';
    if (['customer', 'user'].includes(normalized)) return 'customer';
    if (['operator', 'senior-operator'].includes(normalized)) return 'operator';
    if (['finance', 'financial', 'accountant'].includes(normalized)) return 'finance';
    return normalized;
  };
  const resolveRoleKey = (roles = [], isSuperAdmin = false) => {
    const normalized = Array.isArray(roles) ? roles.map(normalizeRoleNameHelper) : [];
    if (isSuperAdmin || normalized.includes('super-admin') || normalized.includes('super admin')) {
      return 'super-admin';
    }
    if (normalized.includes('administrator') || normalized.includes('admin')) {
      return 'admin';
    }
    if (normalized.includes('bank-employee') || normalized.includes('bank_employee')) {
      return 'bank-employee';
    }
    if (normalized.includes('customer') || normalized.includes('user')) {
      return 'customer';
    }
    if (normalized.includes('operator')) {
      return 'operator';
    }
    if (normalized.includes('finance') || normalized.includes('financial') || normalized.includes('accountant')) {
      return 'finance';
    }
    return 'user';
  };
  const normalizeSidebarEditingGroups = (savedGroups) => {
    const groups = Array.isArray(savedGroups) ? savedGroups : [];
    return groups.map((g) => {
      const items = Array.isArray(g.items) ? g.items : [];
      return {
        id: g.id,
        icon: g.icon ?? '📁',
        label: g.label ?? g.id,
        visible: g.visible !== false,
        expanded: false,
        items: items.map((it) =>
          typeof it === 'string'
            ? { name: it, visible: true }
            : { name: it.name ?? '', visible: it.visible !== false }
        ),
      };
    });
  };
  const buildSidebarConfigFromEditing = (editing) => {
    const parsed = {};
    Object.keys(editing || {}).forEach((roleKey) => {
      parsed[roleKey] = (editing[roleKey] || [])
        .filter((group) => group.visible)
        .map((group) => ({
          id: group.id,
          icon: group.icon,
          label: group.label,
          items: (group.items || []).filter((item) => item.visible).map((item) => item.name),
        }));
    });
    return parsed;
  };

  return {
    sidebarOpen: false,
    loadingUser: true,
    loadingStats: true,
    loadingActivity: true,
    quickActions: [],
    dashboardConfig: {},
    notificationsCount: 3,
    currentUserId: 0,
    isSuperAdmin: false,
    currentUserRoles: [],
    chatRooms: [],
    archivedRooms: [],
    selectedChatRoom: null,
    messages: [],
    messageDraft: '',
    attachmentFile: null,
    loadingChatRooms: false,
    loadingMessages: false,
    brokerRegistrationEnabled: true,
    togglingBrokerRegistration: false,

    sidebarMenuConfig: [],
    sidebarMenuConfigByRole: {},
    sidebarMenuConfigText: '{}',
    sidebarMenuEditing: {},

    activeGroupId: 'dashboard',
    activeItem: 'نمای کلی',

    openGroups: {
      dashboard: true,
      users: false,
      account: false,
      kyc: false,
      ads: false,
      search: false,
      negotiations: false,
      orders: false,
      wallet: false,
      escrow: false,
      contracts: false,
      messages: false,
      ratings: false,
      disputes: false,
      documents: false,
      notifications: false,
      support: false,
    },
    activeSidebarRoleKey: null,

    user: { name: '', email: '', avatarInitial: '', verified: false, vip: false, score: 0, walletBalance: 0 },
    stats: [],
    activity: [],
    resolvedStats: [],

    navGroups: [],
    availableRoles: [],
    availablePermissions: [],
    selectedRoles: [],
    selectedPermissions: [],
    sidebarPreviewRole: '',
    selectedUserProfileId: null,
    userProfileOpen: false,
    userProfileLoading: false,
    userProfileTab: 'details',
    userProfileDetails: null,
    userProfileActivity: [],
    userProfileWallet: null,
    users: [],
    usersLoading: false,
    userPage: 1,
    userLastPage: 1,
    usersPerPage: 15,
    usersTotal: 0,
    userSearch: '',
    userSearchTimer: null,
    userFormOpen: false,
    userFormMode: 'create',
    userFormTab: 'general',
    userForm: {
      id: null,
      name: '',
      email: '',
      mobile: '',
      password: '',
      password_confirmation: '',
      status: 'active',
      is_verified: false,
      is_suspended: false,
      moderation_note: '',
    },
    roleMenus: {},

    _pendingApiRequests: {},
    _apiRequestCache: {},
    _dashboardInitialized: false,

    init() {
      if (this._dashboardInitialized) {
        return;
      }
      this._dashboardInitialized = true;
      this._applyLocalTokenFromStorage();
      this.loadDashboardConfig();
      this.loadDefaultMenus().then(() => {
        this.navGroups = this.getVisibleNavGroups();
      });
      this.fetchRoleOptions();
      this.fetchPermissionOptions();
      this.setupCrudNotifications();
      this.fetchCurrentUser();
      this.fetchDashboardStats();
      this.fetchRecentActivity();
      this.fetchBrokerRegistrationStatus();
    },

    _applyLocalTokenFromStorage() {
      const token = localStorage.getItem('auth_token');
      if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      } else {
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    async loadDashboardConfig() {
      if (this._pendingApiRequests['dashboardConfig']) {
        return this._pendingApiRequests['dashboardConfig'];
      }

      const promise = (async () => {
        try {
          const data = await apiGetRaw('/api/v1/dashboard/config', {});
          this.dashboardConfig = data?.data ?? data ?? {};
          this.quickActions = this.dashboardConfig.quick_actions ?? [];
        } catch (err) {
          console.error('خطا در بارگذاری پیکربندی داشبورد:', err);
          this.dashboardConfig = {};
          this.quickActions = [];
        } finally {
          delete this._pendingApiRequests['dashboardConfig'];
        }
      })();

      this._pendingApiRequests['dashboardConfig'] = promise;
      return promise;
    },

    async loadDefaultMenus() {
      try {
        this.roleMenus = await apiGet('/api/v1/user-management/sidebar-menus/defaults', {});
      } catch (err) {
        console.error('خطا در دریافت منوهای پیش‌فرض از API:', err);
        this.roleMenus = {};
      }
    },

    get activeGroupLabel() {
      const g = this.navGroups.find((g) => g.id === this.activeGroupId);
      return g ? g.label : '';
    },
    get activeGroupIcon() {
      const g = this.navGroups.find((g) => g.id === this.activeGroupId);
      return g ? g.icon : '📄';
    },

    toggleGroup(id) {
      Object.keys(this.openGroups).forEach((key) => {
        this.openGroups[key] = key === id ? !this.openGroups[key] : false;
      });
    },

    toggleSidebarRoleKey(roleKey) {
      this.activeSidebarRoleKey = this.activeSidebarRoleKey === roleKey ? null : roleKey;
    },

    isGroupActive(id) {
      return this.activeGroupId === id;
    },

    selectItem(groupId, item) {
      this.activeGroupId = groupId;
      this.activeItem = item;
      Object.keys(this.openGroups).forEach((key) => {
        this.openGroups[key] = key === groupId;
      });
      this.sidebarOpen = false;

      if (groupId === 'messages') {
        if (item === 'گفتگوها') {
          this.fetchChatRooms();
        } else if (item === 'آرشیو پیام‌ها') {
          this.fetchArchivedRooms();
        }
      }
      if (groupId === 'users') {
        this.userPage = 1;
        this.fetchUsers();
      }
    },

    async fetchChatRooms() {
      this.loadingChatRooms = true;
      try {
        this.chatRooms = await apiGet('/api/v1/chat/rooms', []);
      } catch (err) {
        console.error('خطا در بارگذاری گفتگوها:', err);
        this.chatRooms = [];
      } finally {
        this.loadingChatRooms = false;
      }
    },

    async fetchUsers() {
      this.usersLoading = true;
      try {
        const searchValue = typeof this.userSearch === 'string' ? this.userSearch.trim() : '';
        const data = await apiGetRaw('/api/v1/users', null, {
          params: {
            page: this.userPage,
            per_page: this.usersPerPage,
            search: searchValue || undefined,
          },
        });
        const payload = data?.data ?? data ?? [];
        this.users = Array.isArray(payload) ? payload : [];
        this.userPage = data?.meta?.current_page ?? data?.current_page ?? this.userPage;
        this.userLastPage = data?.meta?.last_page ?? data?.lastPage ?? this.userLastPage;
        this.usersPerPage = data?.meta?.per_page ?? data?.per_page ?? this.usersPerPage;
        this.usersTotal = data?.meta?.total ?? data?.total ?? this.users.length;
      } catch (err) {
        console.error('خطا در بارگذاری کاربران:', err);
        this.users = [];
        this.usersTotal = 0;
      } finally {
        this.usersLoading = false;
      }
    },

    debounceFetchUsers() {
      if (this.userSearchTimer) {
        clearTimeout(this.userSearchTimer);
      }
      this.userSearchTimer = setTimeout(() => {
        this.userPage = 1;
        this.fetchUsers();
      }, 450);
    },

    openCreateUser() {
      this.userFormMode = 'create';
      this.userFormOpen = true;
      this.userFormTab = 'general';
      this.selectedRoles = [];
      this.selectedPermissions = [];
      this.sidebarPreviewRole = '';
      this.userForm = {
        id: null,
        name: '',
        email: '',
        mobile: '',
        password: '',
        password_confirmation: '',
        status: 'active',
        is_verified: false,
        is_suspended: false,
        moderation_note: '',
      };
    },

    openEditUser(user) {
      this.userFormMode = 'edit';
      this.userFormOpen = true;
      this.userFormTab = 'general';
      this.selectedRoles = Array.isArray(user.roles) ? user.roles.slice() : [];
      this.selectedPermissions = Array.isArray(user.permissions) ? user.permissions.slice() : [];
      this.sidebarPreviewRole = Array.isArray(user.roles) && user.roles.length ? user.roles[0] : '';
      this.userForm = {
        id: user.id,
        name: user.name || '',
        email: user.email || '',
        mobile: user.mobile || '',
        password: '',
        password_confirmation: '',
        status: user.status || 'active',
        is_verified: !!user.is_verified,
        is_suspended: !!user.is_suspended,
        moderation_note: user.moderation_note || '',
      };
    },

    openUserProfile(user) {
      this.selectedUserProfileId = user.id;
      this.userProfileOpen = true;
      this.userProfileTab = 'details';
      this.userProfileDetails = null;
      this.userProfileActivity = [];
      this.userProfileWallet = null;
      this.fetchUserProfile(user.id);
    },

    async fetchUserProfile(userId) {
      this.userProfileLoading = true;
      try {
        const payload = await apiGet(`/api/v1/users/${userId}`, {});
        this.userProfileDetails = payload;
        this.userProfileActivity = payload.activity_logs ?? [];
        this.userProfileWallet = payload.wallet ?? null;
      } catch (err) {
        console.error('خطا در بارگذاری پروفایل کاربر:', err);
        this.userProfileDetails = null;
        this.userProfileActivity = [];
        this.userProfileWallet = null;
      } finally {
        this.userProfileLoading = false;
      }
    },

    closeUserProfile() {
      this.userProfileOpen = false;
      this.userProfileDetails = null;
      this.userProfileActivity = [];
      this.userProfileWallet = null;
      this.selectedUserProfileId = null;
    },

    async toggleUserSuspension(user) {
      if (!user?.id) {
        return;
      }
      const action = user.is_suspended ? 'unsuspend' : 'suspend';
      try {
        await apiPost(`/api/v1/users/${user.id}/moderate`, { action });
        this.fetchUsers();
      } catch (err) {
        console.error('خطا در تغییر وضعیت تعلیق:', err);
      }
    },

    async saveUser() {
      try {
        const payload = {
          name: this.userForm.name,
          email: this.userForm.email || null,
          mobile: this.userForm.mobile || null,
          status: this.userForm.status,
          is_verified: this.userForm.is_verified,
          is_suspended: this.userForm.is_suspended,
          moderation_note: this.userForm.moderation_note,
          roles: Array.isArray(this.selectedRoles) ? this.selectedRoles : [],
          permissions: Array.isArray(this.selectedPermissions) ? this.selectedPermissions : [],
        };

        if (this.userForm.password) {
          payload.password = this.userForm.password;
          payload.password_confirmation = this.userForm.password_confirmation;
        }

        if (this.userFormMode === 'edit' && this.userForm.id) {
          await apiPut(`/api/v1/users/${this.userForm.id}`, payload);
        } else {
          await apiPost('/api/v1/users', payload);
        }

        this.userFormOpen = false;
        this.fetchUsers();
      } catch (err) {
        console.error('خطا در ذخیره کاربر:', err);
      }
    },

    async deleteUser(user) {
      if (!user || !user.id) {
        return;
      }

      if (!confirm(`آیا از حذف کاربر "${user.name}" مطمئن هستید؟`)) {
        return;
      }

      try {
        await apiDelete(`/api/v1/users/${user.id}`);
        if (this.users.length === 1 && this.userPage > 1) {
          this.userPage -= 1;
        }
        this.fetchUsers();
      } catch (err) {
        console.error('خطا در حذف کاربر:', err);
      }
    },

    closeUserForm() {
      this.userFormOpen = false;
    },

    setUserPage(page) {
      if (page < 1 || page > this.userLastPage || page === this.userPage) {
        return;
      }
      this.userPage = page;
      this.fetchUsers();
    },

    async fetchRoleOptions() {
      try {
        this.availableRoles = await apiGet('/api/v1/user-management/roles', []);
      } catch (err) {
        console.error('خطا در بارگذاری نقش‌ها:', err);
        this.availableRoles = [];
      }
    },

    async fetchPermissionOptions() {
      try {
        this.availablePermissions = await apiGet('/api/v1/user-management/permissions', []);
      } catch (err) {
        console.error('خطا در بارگذاری دسترسی‌ها:', err);
        this.availablePermissions = [];
      }
    },

    getSidebarRoleKey() {
      return this.sidebarPreviewRole ? canonicalRoleKey(this.sidebarPreviewRole) : null;
    },

    getRoleMenuEditing() {
      const roleKey = this.getSidebarRoleKey();
      if (!roleKey) {
        return [];
      }
      return Array.isArray(this.sidebarMenuEditing?.[roleKey]) ? this.sidebarMenuEditing[roleKey] : [];
    },

    toggleSidebarGroupForSelectedRole(groupId) {
      const roleKey = this.getSidebarRoleKey();
      if (!roleKey) return;
      const group = (this.sidebarMenuEditing[roleKey] || []).find((g) => g.id === groupId);
      if (group) {
        group.visible = !group.visible;
        if (Array.isArray(group.items)) {
          group.items.forEach((item) => {
            item.visible = group.visible;
          });
        }
      }
    },

    toggleSidebarItemForSelectedRole(groupId, itemName) {
      const roleKey = this.getSidebarRoleKey();
      if (!roleKey) return;
      const group = (this.sidebarMenuEditing[roleKey] || []).find((g) => g.id === groupId);
      if (!group) return;
      const item = Array.isArray(group.items) ? group.items.find((i) => i.name === itemName) : null;
      if (item) {
        item.visible = !item.visible;
      }
    },

    async saveSidebarMenuConfigForSelectedRole() {
      const roleKey = this.getSidebarRoleKey();
      if (!roleKey) {
        this.showToast('برای ذخیره منو، یک نقش انتخاب کنید.', 'error');
        return;
      }

      try {
        const updatedConfig = {
          ...this.sidebarMenuConfigByRole,
          [roleKey]: buildSidebarConfigFromEditing({ [roleKey]: this.sidebarMenuEditing[roleKey] || [] })[roleKey],
        };

        const data = await apiPost('/api/v1/user-management/sidebar-menus', { config: updatedConfig }, updatedConfig, { headers: { 'X-No-Crud-Toast': true } });
        this.sidebarMenuConfigByRole = data ?? updatedConfig;
        this.sidebarMenuConfigText = toPrettyJson(this.sidebarMenuConfigByRole);
        this.initSidebarMenuEditing();
        this.showToast('تنظیمات منوی سایدبار ذخیره شد.', 'success');
      } catch (err) {
        console.error('خطا در ذخیره تنظیمات منوی سایدبار:', err);
        this.showToast('خطا در ذخیره تنظیمات منوی سایدبار.', 'error');
      }
    },

    async fetchArchivedRooms() {
      this.loadingChatRooms = true;
      try {
        this.archivedRooms = await apiGet('/api/v1/chat/rooms/archived', []);
      } catch (err) {
        console.error('خطا در بارگذاری آرشیو گفتگوها:', err);
        this.archivedRooms = [];
      } finally {
        this.loadingChatRooms = false;
      }
    },

    async selectChatRoom(room) {
      this.selectedChatRoom = room;
      this.messageDraft = '';
      await this.fetchMessages(room.id);
    },

    async fetchMessages(roomId) {
      if (!roomId) {
        this.messages = [];
        return;
      }
      this.loadingMessages = true;
      try {
        this.messages = await apiGet(`/api/v1/chat/rooms/${roomId}/messages`, []);
      } catch (err) {
        console.error('خطا در دریافت پیام‌ها:', err);
        this.messages = [];
      } finally {
        this.loadingMessages = false;
      }
    },

    async sendChatMessage() {
      if (!this.selectedChatRoom || (!this.messageDraft.trim() && !this.attachmentFile)) {
        return;
      }

      const formData = new FormData();
      formData.append('message', this.messageDraft.trim() || '');
      formData.append('message_type', this.attachmentFile ? 'file' : 'text');

      if (this.attachmentFile) {
        formData.append('attachment', this.attachmentFile);
      }

      try {
        await apiPostForm(`/api/v1/chat/rooms/${this.selectedChatRoom.id}/messages`, formData, null, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        this.messageDraft = '';
        this.attachmentFile = null;
        await this.fetchMessages(this.selectedChatRoom.id);
        await this.fetchChatRooms();
      } catch (err) {
        console.error('خطا در ارسال پیام:', err);
      }
    },

    async archiveSelectedRoom() {
      if (!this.selectedChatRoom) {
        return;
      }
      try {
        await apiPost(`/api/v1/chat/rooms/${this.selectedChatRoom.id}/archive`, {});
        this.selectedChatRoom = null;
        this.messages = [];
        await this.fetchChatRooms();
        await this.fetchArchivedRooms();
      } catch (err) {
        console.error('خطا در بایگانی گفتگو:', err);
      }
    },

    async markSelectedRoomRead() {
      if (!this.selectedChatRoom) {
        return;
      }
      try {
        await apiPost(`/api/v1/chat/rooms/${this.selectedChatRoom.id}/mark-read`, {});
      } catch (err) {
        console.error('خطا در علامت‌گذاری به‌عنوان خوانده‌شده:', err);
      }
    },

    handleAttachmentChange(event) {
      this.attachmentFile = event.target.files?.[0] ?? null;
    },

    chatRoomTitle(room) {
      return room?.name || `گفتگو #${room?.id}`;
    },

    formatRoomDate(value) {
      if (!value) {
        return '';
      }
      return new Date(value).toLocaleString('fa-IR');
    },

    formatToman(n) { return Number(n || 0).toLocaleString('en-US') + ' تومان'; },

    getCurrentUserRoleKey() {
      return resolveRoleKey(this.currentUserRoles, this.isSuperAdmin);
    },

    getVisibleNavGroups() {
      const configured = Array.isArray(this.sidebarMenuConfig) ? this.sidebarMenuConfig : [];
      const roleKey = this.getCurrentUserRoleKey();
      const showUsers = roleKey === 'super-admin';

      return configured
        .filter((group) => showUsers || group.id !== 'users')
        .map((group) => ({
          id: group.id,
          icon: group.icon ?? '',
          label: group.label ?? group.id,
          items: Array.isArray(group.items) ? group.items : [],
        }));
    },

    applyRoleBasedMenu() {
      this.navGroups = this.getVisibleNavGroups();
      if (!this.navGroups.some((group) => group.id === this.activeGroupId)) {
        this.activeGroupId = 'dashboard';
        this.activeItem = 'نمای کلی';
      }
    },


    async loadSidebarMenuConfig() {
      try {
        this.sidebarMenuConfig = await apiGet('/api/v1/user-management/sidebar-menus/me', []);
        this.sidebarMenuConfigText = toPrettyJson(this.sidebarMenuConfig);
        this.applyRoleBasedMenu();
      } catch (err) {
        this.sidebarMenuConfig = [];
        this.sidebarMenuConfigText = toPrettyJson([]);
      }
    },

    async loadFullSidebarMenuConfig() {
      try {
        this.sidebarMenuConfigByRole = await apiGet('/api/v1/user-management/sidebar-menus/config', {});
        this.sidebarMenuConfigText = toPrettyJson(this.sidebarMenuConfigByRole);
        this.initSidebarMenuEditing();
      } catch (err) {
        this.sidebarMenuConfigByRole = {};
        this.sidebarMenuConfigText = toPrettyJson({});
        this.initSidebarMenuEditing();
      }
    },

    initSidebarMenuEditing() {
      this.sidebarMenuEditing = {};
      Object.keys(this.sidebarMenuConfigByRole || {}).forEach((roleKey) => {
        this.sidebarMenuEditing[roleKey] = normalizeSidebarEditingGroups(this.sidebarMenuConfigByRole[roleKey]);
      });
    },

    setupCrudNotifications() {
      if (window.__dashboardCrudNotificationsSetup) {
        return;
      }
      window.__dashboardCrudNotificationsSetup = true;

      axios.interceptors.response.use(
        (response) => {
          const method = String(response.config.method || '').toLowerCase();
          const skipToast = response.config.headers?.['X-No-Crud-Toast'];
          if (!skipToast && ['post', 'put', 'patch', 'delete'].includes(method)) {
            const message = response.data?.message || this.getAutoCrudMessage(method, response.config.url);
            if (message) {
              this.showToast(message, 'success');
            }
          }
          return response;
        },
        (error) => {
          const response = error.response;
          if (response) {
            const method = String(response.config?.method || '').toLowerCase();
            const skipToast = response.config?.headers?.['X-No-Crud-Toast'];
            if (!skipToast && ['post', 'put', 'patch', 'delete'].includes(method)) {
              let message = 'در انجام عملیات خطا رخ داد. لطفاً دوباره تلاش کنید.';
              if (response.data?.message) {
                message = response.data.message;
              } else if (response.status === 422) {
                message = 'اطلاعات وارد شده معتبر نیست.';
              } else if (response.status === 403) {
                message = 'شما اجازه انجام این عملیات را ندارید.';
              }
              this.showToast(message, 'error');
            }
          }
          return Promise.reject(error);
        }
      );
    },

    getAutoCrudMessage(method, url) {
      const action = {
        post: 'با موفقیت ثبت شد.',
        put: 'با موفقیت به‌روزرسانی شد.',
        patch: 'با موفقیت به‌روزرسانی شد.',
        delete: 'با موفقیت حذف شد.',
      }[method] || 'عملیات با موفقیت انجام شد.';

      if (!url) {
        return action;
      }

      if (url.includes('/users') && method === 'post') {
        return 'کاربر جدید با موفقیت ایجاد شد.';
      }
      if (url.includes('/users') && ['put', 'patch'].includes(method)) {
        return 'اطلاعات کاربر با موفقیت به‌روزرسانی شد.';
      }
      if (url.includes('/users') && method === 'delete') {
        return 'کاربر با موفقیت حذف شد.';
      }
      if (url.includes('/user-management/sidebar-menus')) {
        return 'پیکربندی سایدبار با موفقیت ذخیره شد.';
      }
      return action;
    },


    async saveSidebarMenuConfig() {
      try {
        const parsed = buildSidebarConfigFromEditing(this.sidebarMenuEditing || {});
        const data = await apiPost('/api/v1/user-management/sidebar-menus', { config: parsed }, parsed, { headers: { 'X-No-Crud-Toast': true } });
        this.sidebarMenuConfigByRole = data ?? parsed;
        this.sidebarMenuConfigText = toPrettyJson(this.sidebarMenuConfigByRole);
        this.initSidebarMenuEditing();
        this.applyRoleBasedMenu();
        this.showToast('پیکربندی سایدبار ذخیره شد', 'success');
      } catch (err) {
        this.showToast(err?.response?.data?.message || 'خطا در ذخیره پیکربندی سایدبار', 'error');
      }
    },

    async logout() {
      try {
        await apiPost('/api/v1/auth/logout', {});
      } catch (err) {
        // ignore logout failures; still clear local state
      }
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_authenticated');
      delete axios.defaults.headers.common['Authorization'];
      window.location.href = '/users/login';
    },

    async fetchCurrentUser() {
      this.loadingUser = true;
      try {
        const data = await apiGetRaw('/api/v1/auth/me', {});
        const u = data?.user ?? data?.data ?? data;
        const roleNames = Array.isArray(data?.role_names) ? data.role_names : (Array.isArray(u?.roles) ? u.roles.map((role) => role?.name || role) : []);
        const isSuperAdmin = !!(data?.is_super_admin || u?.is_super_admin || roleNames.some((role) => String(role?.name || role).toLowerCase() === 'super admin'));
        this.currentUserRoles = roleNames;
        this.user = {
          name: u.name || '',
          email: u.email || '',
          avatarInitial: (u.name || '?')[0],
          verified: !!u.verified,
          vip: !!u.vip,
          score: u.score ?? 0,
          walletBalance: u.wallet_balance ?? 0,
        };
        this.currentUserId = u.id ?? u.user?.id ?? 0;
        this.isSuperAdmin = isSuperAdmin;
        this.applyRoleBasedMenu();
        await this.loadSidebarMenuConfig();

        if (this.isSuperAdmin || roleNames.includes('administrator') || roleNames.includes('admin')) {
          await this.loadFullSidebarMenuConfig();
        }
      } catch (err) {
        const status = err?.response?.status;
        if (status === 401 || status === 403) {
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user_authenticated');
          delete axios.defaults.headers.common['Authorization'];
          window.location.href = '/users/login';
          return;
        }
        console.error('خطا در دریافت اطلاعات کاربر:', err);
      } finally {
        this.loadingUser = false;
      }
    },

    async fetchBrokerRegistrationStatus() {
      try {
        const data = await apiGetRaw('/api/v1/auth/broker-registration-status', {});
        this.brokerRegistrationEnabled = data?.enabled ?? true;
      } catch (err) {
        console.error('خطا در دریافت وضعیت عضویت اربران:', err);
      }
    },

    async toggleBrokerRegistration() {
      this.togglingBrokerRegistration = true;
      try {
        const data = await apiPostRaw('/api/v1/auth/broker-registration-toggle', {
          enabled: !this.brokerRegistrationEnabled,
        }, {});
        this.brokerRegistrationEnabled = !!data?.enabled;
        this.showToast(data?.message || 'تغییر وضعیت عضویت انجام شد', 'success');
      } catch (err) {
        const msg = err?.response?.data?.message || 'خطا در تغییر وضعیت عضویت';
        this.showToast(msg, 'error');
      } finally {
        this.togglingBrokerRegistration = false;
      }
    },

    showToast(message, type = 'info') {
      const bg = type === 'success' ? '#16a34a' : (type === 'error' ? '#dc2626' : '#0ea5a0');
      Toastify({
        text: message,
        duration: 4500,
        gravity: 'top',
        position: 'center',
        close: true,
        style: { background: bg, color: '#fff' }
      }).showToast();
    },

    async fetchDashboardStats() {
      this.loadingStats = true;

      try {
        this.stats = await apiGet('/api/v1/dashboard/stats', []);
        this.resolvedStats = this.buildResolvedStats(this.stats);
      } catch (err) {
        console.error('خطا در دریافت آمار داشبورد:', err);
        this.stats = [];
        this.resolvedStats = [];
      } finally {
        this.loadingStats = false;
      }
    },

    buildResolvedStats(statsPayload) {
      const configStats = this.dashboardConfig.stats ?? [];
      const payload = Array.isArray(statsPayload) ? statsPayload : [];
      const mapped = {};

      payload.forEach((item) => {
        mapped[item.key] = item;
      });

      return configStats.map((item) => {
        const source = mapped[item.key] ?? {};
        return {
          key: item.key,
          label: item.label,
          bg: item.bg,
          icon: item.icon,
          value: source.value ?? source.label ?? '0',
        };
      });
    },

    async fetchRecentActivity() {
      this.loadingActivity = true;

      try {
        this.activity = await apiGet('/api/v1/dashboard/activity', []);
      } catch (err) {
        console.error('خطا در دریافت فعالیت‌های اخیر:', err);
        this.activity = [];
      } finally {
        this.loadingActivity = false;
      }
    },
  };
}
</script>

</body>
</html>
