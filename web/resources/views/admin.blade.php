<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oromgo - Admin Boshqaruv & Murojaatlar Paneli</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .admin-layout {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: #f1f5f9;
    }
    .admin-navbar {
      background: #0f172a;
      color: white;
      padding: 0.85rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .admin-brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 1.25rem;
      font-weight: 800;
      color: white;
      text-decoration: none;
    }
    .admin-brand .badge {
      background: #7c3aed;
      color: white;
      font-size: 0.75rem;
      padding: 0.2rem 0.6rem;
      border-radius: 9999px;
      font-weight: 700;
    }
    .admin-main {
      max-width: 1400px;
      width: 100%;
      margin: 1.5rem auto 3rem;
      padding: 0 1.5rem;
      flex: 1;
    }
    .admin-nav-tabs {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
      border-bottom: 2px solid var(--border);
      padding-bottom: 0.5rem;
    }
    .admin-nav-tab {
      padding: 0.65rem 1.25rem;
      font-size: 0.95rem;
      font-weight: 700;
      border-radius: var(--radius-md);
      background: transparent;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: var(--transition-fast);
    }
    .admin-nav-tab.active {
      background: white;
      color: #7c3aed;
      box-shadow: var(--shadow-sm);
    }
    .admin-stat-card {
      background: white;
      border-radius: var(--radius-md);
      padding: 1.25rem;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      transition: var(--transition-fast);
    }
    .admin-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }
    .admin-dacha-table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: var(--radius-lg);
      overflow: hidden;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-sm);
    }
    .admin-dacha-table th {
      background: #f8fafc;
      padding: 1rem 1.25rem;
      text-align: left;
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      border-bottom: 1px solid var(--border);
    }
    .admin-dacha-table td {
      padding: 1.15rem 1.25rem;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
      font-size: 0.9rem;
    }
    .admin-dacha-table tr:hover td {
      background: #f8fafc;
    }
    .gallery-preview {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap: 0.65rem;
      margin: 1rem 0;
    }
    .gallery-preview img {
      width: 100%;
      height: 95px;
      object-fit: cover;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    .gallery-preview img:hover {
      transform: scale(1.03);
    }

    /* Admin Chat View Styles */
    .admin-chat-layout {
      display: grid;
      grid-template-columns: 340px 1fr;
      gap: 1.25rem;
      height: 72vh;
      background: white;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }
    .conversations-sidebar {
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      background: #f8fafc;
    }
    .conversation-item {
      padding: 0.9rem 1.15rem;
      border-bottom: 1px solid var(--border);
      cursor: pointer;
      transition: background 0.15s ease;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .conversation-item:hover {
      background: #f1f5f9;
    }
    .conversation-item.active {
      background: #ede9fe;
      border-left: 4px solid #7c3aed;
    }
    .chat-messages-pane {
      display: flex;
      flex-direction: column;
      background: white;
    }
    .chat-messages-body {
      flex: 1;
      padding: 1.25rem;
      overflow-y: auto;
      background: #f1f5f9;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    .admin-bubble-user {
      align-self: flex-start;
      background: white;
      color: var(--dark);
      border: 1px solid var(--border);
      padding: 0.75rem 1rem;
      border-radius: 16px;
      border-bottom-left-radius: 4px;
      max-width: 70%;
      box-shadow: var(--shadow-sm);
      word-break: break-word;
    }
    .admin-bubble-admin {
      align-self: flex-end;
      background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
      color: white;
      padding: 0.75rem 1rem;
      border-radius: 16px;
      border-bottom-right-radius: 4px;
      max-width: 70%;
      box-shadow: 0 3px 10px rgba(124, 58, 237, 0.25);
      word-break: break-word;
    }
  </style>
</head>
<body class="admin-layout">

  <!-- ADMIN NAVBAR -->
  <header class="admin-navbar">
    <div style="display: flex; align-items: center; gap: 1.5rem;">
      <a href="/admin" class="admin-brand">
        <span>🛡️</span>
        <span>Oromgo Admin</span>
        <span class="badge">Boshqaruv</span>
      </a>
      <a href="/" class="btn btn-outline" style="color: #cbd5e1; border-color: #475569; padding: 0.4rem 0.85rem; font-size: 0.85rem;" target="_blank">
        🌐 Asosiy saytni ko'rish
      </a>
    </div>

    <div style="display: flex; align-items: center; gap: 1rem;">
      <span id="adminUserName" style="font-weight: 700; font-size: 0.9rem; color: #f8fafc;">👤 Admin</span>
      <button class="btn btn-outline" style="color: #ef4444; border-color: #ef4444; padding: 0.4rem 0.85rem; font-size: 0.85rem;" onclick="adminLogout()">
        🚪 Chiqish
      </button>
    </div>
  </header>

  <!-- MAIN ADMIN DASHBOARD -->
  <main class="admin-main">
    <!-- SECTION TABS -->
    <div class="admin-nav-tabs">
      <button id="tabDachasBtn" class="admin-nav-tab active" onclick="switchSection('dachas')">
        <span>🏡</span> <span>Dachalar Moderatsiyasi</span>
      </button>
      <button id="tabSupportBtn" class="admin-nav-tab" onclick="switchSection('support')">
        <span>💬</span> <span>Foydalanuvchilar Murojaatlari</span>
        <span id="adminUnreadSupportBadge" style="background: #ef4444; color: white; padding: 2px 7px; border-radius: 9999px; font-size: 0.72rem; font-weight: 800; display: none;">0</span>
      </button>
    </div>

    <!-- ==========================================
         SECTION 1: DACHALAR MODERATSIYASI
         ========================================== -->
    <div id="sectionDachas">
      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h1 style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">Dachalarni Moderatsiya Qilish</h1>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
            Yangi joylangan e'lonlarni ko'rib chiqish, tasdiqlash (faol qilish) yoki nofaol holatga o'tkazish.
          </p>
        </div>

        <button class="btn btn-primary" onclick="loadAdminData()" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
          🔄 Yangilash
        </button>
      </div>

      <!-- STATS CARDS -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;">
        <div class="admin-stat-card" style="border-left: 4px solid #3b82f6;">
          <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Jami E'lonlar</span>
          <h2 id="statTotal" style="font-size: 2rem; font-weight: 800; color: #0f172a;">0</h2>
        </div>
        <div class="admin-stat-card" style="border-left: 4px solid #eab308; background: #fffdf5;">
          <span style="font-size: 0.8rem; color: #a16207; font-weight: 700; text-transform: uppercase;">⏳ Moderatsiyada (Pending)</span>
          <h2 id="statPending" style="font-size: 2rem; font-weight: 800; color: #a16207;">0</h2>
        </div>
        <div class="admin-stat-card" style="border-left: 4px solid #22c55e; background: #f6fef9;">
          <span style="font-size: 0.8rem; color: #15803d; font-weight: 700; text-transform: uppercase;">🟢 Faol Dachalar (Saytda)</span>
          <h2 id="statActive" style="font-size: 2rem; font-weight: 800; color: #15803d;">0</h2>
        </div>
        <div class="admin-stat-card" style="border-left: 4px solid #ef4444; background: #fef8f8;">
          <span style="font-size: 0.8rem; color: #b91c1c; font-weight: 700; text-transform: uppercase;">⏸️ Nofaol / Rad etilgan</span>
          <h2 id="statInactive" style="font-size: 2rem; font-weight: 800; color: #b91c1c;">0</h2>
        </div>
      </div>

      <!-- FILTER & SEARCH BAR -->
      <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;" id="adminFilterTabs">
          <button class="pill-btn active" onclick="setAdminFilter(this, 'all')">Barchasi</button>
          <button class="pill-btn" onclick="setAdminFilter(this, 'pending')" style="color: #a16207; border-color: #fef08a;">⏳ Kutilayotganlar</button>
          <button class="pill-btn" onclick="setAdminFilter(this, 'active')" style="color: #15803d; border-color: #bbf7d0;">🟢 Faol dachalar</button>
          <button class="pill-btn" onclick="setAdminFilter(this, 'inactive')" style="color: #b91c1c; border-color: #fecdd3;">⏸️ Nofaol</button>
        </div>

        <div style="min-width: 300px; flex: 1; max-width: 450px;">
          <input type="text" id="adminSearch" class="form-control" placeholder="🔍 Dacha nomi yoki egasining ismi / telefoni..." oninput="debounceSearch()" style="padding: 0.65rem 1rem;" />
        </div>
      </div>

      <!-- DACHAS TABLE -->
      <div style="overflow-x: auto;">
        <table class="admin-dacha-table">
          <thead>
            <tr>
              <th style="width: 70px;">Rasm</th>
              <th>Dacha nomi & Manzil</th>
              <th>Egasi (Telefon)</th>
              <th>Narx & Sig'im</th>
              <th style="text-align: right;">Harakatlar</th>
            </tr>
          </thead>
          <tbody id="adminTableBody">
            <tr>
              <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                Ma'lumotlar yuklanmoqda...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ==========================================
         SECTION 2: FOYDALANUVCHILAR MUROJAATLARI (CHAT)
         ========================================== -->
    <div id="sectionSupport" style="display: none;">
      <div style="margin-bottom: 1rem;">
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">Adminga Kelgan Murojaatlar</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
          Mijozlar va dacha egalarining savollari va xabarlariga jonli javob qaytarish.
        </p>
      </div>

      <div class="admin-chat-layout">
        <!-- Sidebar: Conversations List -->
        <div class="conversations-sidebar">
          <div style="padding: 0.85rem 1rem; border-bottom: 1px solid var(--border); font-weight: 800; font-size: 0.9rem; color: var(--dark); display: flex; justify-content: space-between; align-items: center;">
            <span>Suhbatlar ro'yxati</span>
            <button class="btn btn-outline" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;" onclick="loadSupportConversations()">🔄</button>
          </div>
          <div id="conversationsList" style="flex: 1; overflow-y: auto;">
            <!-- Injected via JS -->
          </div>
        </div>

        <!-- Main Chat Pane -->
        <div class="chat-messages-pane">
          <!-- Active Chat Header -->
          <div id="activeChatHeader" style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div id="activeChatUserInfo">
              <span style="color: var(--text-muted); font-size: 0.9rem;">Suhbatni boshlash uchun chap tomondan foydalanuvchini tanlang</span>
            </div>
          </div>

          <!-- Messages Body -->
          <div id="adminChatBody" class="chat-messages-body">
            <div style="text-align: center; margin: auto; color: var(--text-muted);">
              💬 Chap tarafdagi ro'yxatdan foydalanuvchi murojaatini tanlang.
            </div>
          </div>

          <!-- Reply Footer -->
          <div id="adminChatFooter" style="padding: 0.85rem 1.25rem; border-top: 1px solid var(--border); display: none;">
            <form id="adminReplyForm" onsubmit="handleAdminReply(event)" style="display: flex; gap: 0.75rem;">
              <input type="text" id="adminReplyInput" class="form-control" placeholder="Foydalanuvchiga javob yozing..." required autocomplete="off" style="padding: 0.75rem 1rem; border-radius: var(--radius-full);" />
              <button type="submit" id="adminReplyBtn" class="btn btn-primary" style="border-radius: var(--radius-full); padding: 0.75rem 1.35rem; font-weight: 700; white-space: nowrap;">
                <span>Javob berish</span> ✈️
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- ==========================================
       MODAL 1: STATUS CHANGE CONFIRMATION (TASDIQLASH OYNASI)
       ========================================== -->
  <div class="modal-backdrop" id="confirmStatusModal">
    <div class="modal-content" style="max-width: 520px; padding: 1.75rem; border-radius: var(--radius-xl);">
      <button class="modal-close" onclick="closeConfirmModal()">✕</button>
      
      <div style="text-align: center; margin-bottom: 1.25rem;">
        <div id="confirmIcon" style="font-size: 2.75rem; margin-bottom: 0.5rem;">❓</div>
        <h3 id="confirmTitle" style="font-size: 1.3rem; font-weight: 800; color: #0f172a;">E'lon holatini o'zgartirish</h3>
        <p id="confirmSubtitle" style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">Ushbu amalni tasdiqlaysizmi?</p>
      </div>

      <!-- Dacha mini summary card with image -->
      <div style="background: var(--bg-page); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.85rem 1rem; display: flex; gap: 1rem; align-items: center; margin-bottom: 1.25rem;">
        <img id="confirmDachaImg" src="" alt="" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);" />
        <div>
          <h4 id="confirmDachaName" style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.2rem;">-</h4>
          <p id="confirmDachaOwner" style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.15rem;">👤 Egasi: -</p>
          <p id="confirmDachaPrice" style="font-size: 0.85rem; font-weight: 700; color: var(--primary);">💰 Narx: -</p>
        </div>
      </div>

      <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-outline" style="flex: 1; padding: 0.75rem;" onclick="closeConfirmModal()">
          Bekor qilish
        </button>
        <button id="confirmActionBtn" class="btn" style="flex: 1; padding: 0.75rem; font-weight: 800; color: white;" onclick="executeConfirmedStatusChange()">
          Ha, tasdiqlayman
        </button>
      </div>
    </div>
  </div>

  <!-- ==========================================
       MODAL 2: DACHA BATAFSIL KO'RISH & MODERATSIYA
       ========================================== -->
  <div class="modal-backdrop" id="dachaDetailModal">
    <div class="modal-content" style="max-width: 880px; padding: 0; max-height: 90vh; overflow-y: auto;">
      <!-- Header -->
      <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: white; sticky: top;">
        <div>
          <span id="detailStatusBadge" class="status-badge-pending">⏳ Kutilmoqda</span>
          <h2 id="detailName" style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-top: 0.35rem;">-</h2>
        </div>
        <button class="modal-close" style="position: static;" onclick="closeDetailModal()">✕</button>
      </div>

      <div style="padding: 1.5rem 1.75rem;">
        <!-- Images Gallery -->
        <div>
          <h4 style="font-size: 0.9rem; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 0.5rem;">📸 Rasmlar Galereyasi</h4>
          <div id="detailGallery" class="gallery-preview">
            <!-- Injected via JS -->
          </div>
        </div>

        <!-- Info Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-top: 1.25rem; background: var(--bg-page); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border);">
          <div>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">📍 Joylashuv</span>
            <p id="detailLocation" style="font-weight: 700; color: #0f172a; margin-top: 0.2rem;">-</p>
            <p id="detailAddress" style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.1rem;">-</p>
          </div>
          <div>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">💰 Narxlar</span>
            <p id="detailPrices" style="font-weight: 800; color: var(--primary); margin-top: 0.2rem;">-</p>
          </div>
          <div>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">👥 Sig'im va Xonalar</span>
            <p id="detailCapacity" style="font-weight: 700; color: #0f172a; margin-top: 0.2rem;">-</p>
          </div>
          <div>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">👤 Dacha Egasi</span>
            <p id="detailOwner" style="font-weight: 700; color: #0284c7; margin-top: 0.2rem;">-</p>
          </div>
        </div>

        <!-- Description -->
        <div style="margin-top: 1.25rem;">
          <h4 style="font-size: 0.9rem; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 0.4rem;">📝 Tavsif</h4>
          <p id="detailDescription" style="font-size: 0.9rem; color: var(--dark); line-height: 1.6; background: white; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">-</p>
        </div>

        <!-- Amenities -->
        <div style="margin-top: 1.25rem;">
          <h4 style="font-size: 0.9rem; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 0.5rem;">✨ Mavjud Qulayliklar</h4>
          <div id="detailAmenities" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <!-- Amenities badges -->
          </div>
        </div>
      </div>

      <!-- Action Footer -->
      <div style="padding: 1.25rem 1.75rem; border-top: 1px solid var(--border); background: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <button class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5;" onclick="deleteDachaFromDetail()">
          🗑️ E'lonni butunlay o'chirish
        </button>

        <div id="detailActionButtons" style="display: flex; gap: 0.5rem;">
          <!-- Injected via JS based on status -->
        </div>
      </div>
    </div>
  </div>

  <script>
    const API_BASE = '/api';
    let adminToken = localStorage.getItem('oromgo_token') || '';
    let adminUser = JSON.parse(localStorage.getItem('oromgo_user') || 'null');
    let currentFilter = 'all';
    let searchQuery = '';
    let searchTimer = null;
    let loadedDachas = [];
    let currentSelectedDacha = null;

    let pendingAction = {
      dachaId: null,
      newStatus: null
    };

    // Chat states
    let activeChatUserId = null;
    let chatPollingTimer = null;

    document.addEventListener('DOMContentLoaded', () => {
      if (!adminToken || !adminUser || (adminUser.role !== 'admin' && adminUser.role !== 'super_admin')) {
        loginAdminAuto();
        return;
      }

      document.getElementById('adminUserName').textContent = `👤 ${adminUser.name}`;
      loadAdminData();
      loadSupportConversations();

      // Poll support unread count every 10s
      setInterval(() => {
        loadSupportConversations(false);
      }, 10000);
    });

    function switchSection(section) {
      const secDachas = document.getElementById('sectionDachas');
      const secSupport = document.getElementById('sectionSupport');
      const tabDachas = document.getElementById('tabDachasBtn');
      const tabSupport = document.getElementById('tabSupportBtn');

      if (section === 'dachas') {
        secDachas.style.display = 'block';
        secSupport.style.display = 'none';
        tabDachas.classList.add('active');
        tabSupport.classList.remove('active');
        loadAdminData();
      } else {
        secDachas.style.display = 'none';
        secSupport.style.display = 'block';
        tabDachas.classList.remove('active');
        tabSupport.classList.add('active');
        loadSupportConversations();
      }
    }

    async function loginAdminAuto() {
      try {
        const res = await fetch(`${API_BASE}/demo-login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ role: 'admin' })
        });
        if (res.ok) {
          const data = await res.json();
          adminToken = data.token;
          adminUser = data.user;
          localStorage.setItem('oromgo_token', adminToken);
          localStorage.setItem('oromgo_user', JSON.stringify(adminUser));
          document.getElementById('adminUserName').textContent = `👤 ${adminUser.name}`;
          loadAdminData();
          loadSupportConversations();
        } else {
          window.location.href = '/';
        }
      } catch (e) {
        window.location.href = '/';
      }
    }

    async function loadAdminData() {
      await Promise.all([loadStats(), loadDachas()]);
    }

    async function loadStats() {
      try {
        const res = await fetch(`${API_BASE}/admin/stats`, {
          headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          const stats = await res.json();
          document.getElementById('statTotal').textContent = stats.total || 0;
          document.getElementById('statPending').textContent = stats.pending || 0;
          document.getElementById('statActive').textContent = stats.active || 0;
          document.getElementById('statInactive').textContent = stats.inactive || 0;
        }
      } catch (err) {
        console.error('Stats error:', err);
      }
    }

    async function loadDachas() {
      const tbody = document.getElementById('adminTableBody');
      tbody.innerHTML = `
        <tr>
          <td colspan="5" style="text-align: center; padding: 3rem;">
            <div style="display:inline-block; width: 30px; height: 30px; border: 3px solid var(--border); border-top-color: #7c3aed; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
            <p style="margin-top: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">E'lonlar yuklanmoqda...</p>
          </td>
        </tr>
      `;

      try {
        const params = new URLSearchParams();
        if (currentFilter !== 'all') params.append('status', currentFilter);
        if (searchQuery) params.append('q', searchQuery);

        const res = await fetch(`${API_BASE}/admin/dachas?${params.toString()}`, {
          headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('E\'lonlarni yuklab bo\'lmadi');
        const result = await res.json();
        loadedDachas = result.data || [];

        renderTable(loadedDachas);
      } catch (err) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" style="text-align: center; padding: 2.5rem; color: #ef4444; font-weight: 600;">
              Ma'lumotlarni yuklashda xatolik yuz berdi.
            </td>
          </tr>
        `;
      }
    }

    function renderTable(dachas) {
      const tbody = document.getElementById('adminTableBody');
      if (dachas.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" style="text-align: center; padding: 3.5rem; color: var(--text-muted);">
              🏖️ Tanlangan holat bo'yicha e'lonlar mavjud emas.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = dachas.map(d => {
        const img = (d.media && d.media.length > 0)
          ? `/storage/${d.media[0].path}`
          : 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=400&q=80';

        const ownerName = d.owner ? d.owner.name : 'Noma\'lum';
        const ownerPhone = d.owner ? d.owner.phone : '-';

        return `
          <tr>
            <td>
              <img src="${img}" alt="${escapeHtml(d.name)}" onclick="openDachaDetail(${d.id})" style="width: 56px; height: 56px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border); cursor: pointer;" title="Batafsil ko'rish uchun bosing" />
            </td>
            <td>
              <div style="font-weight: 800; color: #0f172a; margin-bottom: 0.2rem; cursor: pointer;" onclick="openDachaDetail(${d.id})" title="Batafsil ko'rish uchun bosing">${escapeHtml(d.name)}</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);">📍 ${escapeHtml(d.region || '')}, ${escapeHtml(d.district || '')} ${d.mahalla ? `(${escapeHtml(d.mahalla)})` : ''}</div>
            </td>
            <td>
              <div style="font-weight: 700; color: #0284c7; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;" onclick="openDachaDetail(${d.id})" title="Dacha egasi va batafsil ma'lumotlarni ko'rish">
                <span>👤 ${escapeHtml(ownerName)}</span>
                <span style="font-size: 0.75rem; color: #64748b;">ℹ️</span>
              </div>
              <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">📞 ${escapeHtml(ownerPhone)}</div>
            </td>
            <td>
              <div style="font-weight: 800; color: #0f172a;">${Number(d.weekday_price).toLocaleString()} ${d.currency || 'USD'}</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);">👥 ${d.capacity || 1} kishi | 🚪 ${d.rooms_count || 1} xona</div>
            </td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                ${d.status !== 'active' ? `
                  <button class="btn" style="background: #16a34a; color: white; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 700;" onclick="promptStatusChange(${d.id}, 'active')">
                    ✅ Faollashtirish
                  </button>
                ` : ''}
                ${d.status !== 'inactive' ? `
                  <button class="btn" style="background: #eab308; color: #713f12; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 700;" onclick="promptStatusChange(${d.id}, 'inactive')">
                    ⏸️ Nofaol qilish
                  </button>
                ` : ''}
                <button class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; padding: 0.45rem 0.65rem; font-size: 0.8rem;" onclick="deleteDacha(${d.id})" title="O'chirish">
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    function setAdminFilter(btn, status) {
      document.querySelectorAll('#adminFilterTabs .pill-btn').forEach(b => b.classList.remove('active'));
      if (btn) btn.classList.add('active');
      currentFilter = status;
      loadDachas();
    }

    function debounceSearch() {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        searchQuery = document.getElementById('adminSearch').value.trim();
        loadDachas();
      }, 350);
    }

    // ==========================================
    // CONFIRMATION MODAL LOGIC
    // ==========================================
    function promptStatusChange(id, targetStatus) {
      const d = loadedDachas.find(item => item.id === id);
      if (!d) return;

      pendingAction.dachaId = id;
      pendingAction.newStatus = targetStatus;

      const img = (d.media && d.media.length > 0)
        ? `/storage/${d.media[0].path}`
        : 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=400&q=80';

      document.getElementById('confirmDachaImg').src = img;
      document.getElementById('confirmDachaName').textContent = d.name;
      document.getElementById('confirmDachaOwner').textContent = `👤 Egasi: ${d.owner ? d.owner.name : 'Noma\'lum'} (${d.owner ? d.owner.phone : '-'})`;
      document.getElementById('confirmDachaPrice').textContent = `💰 Narx: ${Number(d.weekday_price).toLocaleString()} ${d.currency || 'USD'}`;

      const iconEl = document.getElementById('confirmIcon');
      const titleEl = document.getElementById('confirmTitle');
      const subtitleEl = document.getElementById('confirmSubtitle');
      const btnEl = document.getElementById('confirmActionBtn');

      if (targetStatus === 'active') {
        iconEl.textContent = '✅';
        titleEl.textContent = 'E\'lonni faollashtirmoqchimisiz?';
        subtitleEl.textContent = 'E\'lon tasdiqlangach, u darhol barcha foydalanuvchilarga saytda ko\'rinadi.';
        btnEl.textContent = 'Ha, faollashtirilsin';
        btnEl.style.background = '#16a34a';
      } else if (targetStatus === 'inactive') {
        iconEl.textContent = '⏸️';
        titleEl.textContent = 'E\'lonni nofaol qilmoqchimisiz?';
        subtitleEl.textContent = 'E\'lon nofaol qilingach, saytdan va qidiruvdan yashiriladi.';
        btnEl.textContent = 'Ha, nofaol qilinsin';
        btnEl.style.background = '#eab308';
        btnEl.style.color = '#713f12';
      }

      document.getElementById('confirmStatusModal').classList.add('open');
    }

    function closeConfirmModal() {
      document.getElementById('confirmStatusModal').classList.remove('open');
      pendingAction.dachaId = null;
      pendingAction.newStatus = null;
    }

    async function executeConfirmedStatusChange() {
      if (!pendingAction.dachaId || !pendingAction.newStatus) return;

      const { dachaId, newStatus } = pendingAction;
      closeConfirmModal();

      await executeStatusUpdate(dachaId, newStatus);
    }

    async function executeStatusUpdate(id, newStatus) {
      try {
        const res = await fetch(`${API_BASE}/admin/dachas/${id}/status`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${adminToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ status: newStatus })
        });

        if (res.ok) {
          await loadAdminData();
          if (currentSelectedDacha && currentSelectedDacha.id === id) {
            currentSelectedDacha.status = newStatus;
            renderDetailActionButtons(currentSelectedDacha);
          }
        } else {
          alert('Statusni o\'zgartirishda xatolik yuz berdi');
        }
      } catch (e) {
        alert('Server bilan bog\'lanishda xatolik');
      }
    }

    // ==========================================
    // DACHA DETAIL MODAL LOGIC
    // ==========================================
    function openDachaDetail(id) {
      const d = loadedDachas.find(item => item.id === id);
      if (!d) return;

      currentSelectedDacha = d;

      document.getElementById('detailName').textContent = d.name;

      // Status Badge
      const statusBadge = document.getElementById('detailStatusBadge');
      if (d.status === 'pending') {
        statusBadge.className = 'status-badge-pending';
        statusBadge.textContent = '⏳ Moderatsiyada (Kutilmoqda)';
      } else if (d.status === 'active') {
        statusBadge.className = 'status-badge-active';
        statusBadge.textContent = '🟢 Faol (Saytda ko\'rinmoqda)';
      } else {
        statusBadge.className = 'status-badge-inactive';
        statusBadge.textContent = '⏸️ Nofaol / To\'xtatilgan';
      }

      // Gallery
      const galleryEl = document.getElementById('detailGallery');
      if (d.media && d.media.length > 0) {
        galleryEl.innerHTML = d.media.map(m => `
          <a href="/storage/${m.path}" target="_blank" title="Kattalashtirib ko'rish">
            <img src="/storage/${m.path}" alt="${escapeHtml(d.name)}" />
          </a>
        `).join('');
      } else {
        galleryEl.innerHTML = `<p style="color: var(--text-muted); font-size: 0.85rem;">Rasmlar yuklanmagan.</p>`;
      }

      // Details
      document.getElementById('detailLocation').textContent = `${d.region || ''}, ${d.district || ''}`;
      document.getElementById('detailAddress').textContent = `${d.mahalla ? `Mahalla: ${d.mahalla}` : ''} ${d.address ? `| Manzil: ${d.address}` : ''}`;
      document.getElementById('detailPrices').textContent = `Ish kunlari: ${Number(d.weekday_price).toLocaleString()} ${d.currency || 'USD'} | Dam olish: ${d.weekend_price ? Number(d.weekend_price).toLocaleString() + ' ' + (d.currency || 'USD') : 'Kiritilmagan'}`;
      document.getElementById('detailCapacity').textContent = `${d.capacity || 1} kishilik | ${d.rooms_count || 1} ta xona`;
      document.getElementById('detailOwner').textContent = `${d.owner ? d.owner.name : 'Noma\'lum'} (📞 ${d.owner ? d.owner.phone : '-'} | ✉️ ${d.owner ? d.owner.email : '-'})`;
      document.getElementById('detailDescription').textContent = d.description || 'Tavsif kiritilmagan.';

      // Amenities
      const amenitiesEl = document.getElementById('detailAmenities');
      if (d.amenities && d.amenities.length > 0) {
        amenitiesEl.innerHTML = d.amenities.map(a => `
          <span style="background: white; border: 1px solid var(--border); padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; color: #0f172a;">
            ${a.icon || '✨'} ${escapeHtml(a.name)}
          </span>
        `).join('');
      } else {
        amenitiesEl.innerHTML = `<span style="color: var(--text-muted); font-size: 0.85rem;">Qulayliklar belgilanmagan.</span>`;
      }

      renderDetailActionButtons(d);

      document.getElementById('dachaDetailModal').classList.add('open');
    }

    function renderDetailActionButtons(d) {
      const container = document.getElementById('detailActionButtons');
      container.innerHTML = `
        ${d.status !== 'active' ? `
          <button class="btn" style="background: #16a34a; color: white; padding: 0.55rem 1.15rem; font-size: 0.85rem; font-weight: 700;" onclick="changeStatusFromDetail(${d.id}, 'active')">
            ✅ Faollashtirish (Saytda chiqarish)
          </button>
        ` : ''}
        ${d.status !== 'inactive' ? `
          <button class="btn" style="background: #eab308; color: #713f12; padding: 0.55rem 1.15rem; font-size: 0.85rem; font-weight: 700;" onclick="changeStatusFromDetail(${d.id}, 'inactive')">
            ⏸️ Nofaol qilish
          </button>
        ` : ''}
      `;
    }

    function closeDetailModal() {
      document.getElementById('dachaDetailModal').classList.remove('open');
      currentSelectedDacha = null;
    }

    async function changeStatusFromDetail(id, status) {
      await executeStatusUpdate(id, status);
      const statusNames = { active: 'faollashtirildi (saytda ko\'rinmoqda)', inactive: 'nofaol holatga o\'tkazildi' };
      alert(`Dacha e'loni muvaffaqiyatli ${statusNames[status]}!`);
    }

    async function deleteDachaFromDetail() {
      if (!currentSelectedDacha) return;
      const id = currentSelectedDacha.id;
      if (!confirm('Haqiqatan ham ushbu dacha e\'lonini butunlay o\'chirmoqchimisiz?')) return;

      closeDetailModal();
      await deleteDacha(id);
    }

    async function deleteDacha(id) {
      if (!confirm('Haqiqatan ham ushbu dachani o\'chirmoqchimisiz?')) return;
      try {
        const res = await fetch(`${API_BASE}/admin/dachas/${id}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          loadAdminData();
        }
      } catch (e) {
        alert('O\'chirishda xatolik');
      }
    }

    // ==========================================
    // ADMIN SUPPORT CHAT LOGIC
    // ==========================================
    async function loadSupportConversations(render = true) {
      if (!adminToken) return;
      try {
        const res = await fetch(`${API_BASE}/admin/support/chats`, {
          headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          const chats = await res.json();
          
          let totalUnread = 0;
          chats.forEach(c => { totalUnread += (c.unread_count || 0); });
          const badgeEl = document.getElementById('adminUnreadSupportBadge');
          if (badgeEl) {
            badgeEl.textContent = totalUnread;
            badgeEl.style.display = totalUnread > 0 ? 'inline-block' : 'none';
          }

          if (render) {
            renderConversationsList(chats);
          }
        }
      } catch (err) {
        console.error('loadSupportConversations error:', err);
      }
    }

    function renderConversationsList(chats) {
      const container = document.getElementById('conversationsList');
      if (chats.length === 0) {
        container.innerHTML = `
          <div style="padding: 2rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
            Hozircha murojaatlar yo'q.
          </div>
        `;
        return;
      }

      container.innerHTML = chats.map(c => {
        const isActive = activeChatUserId === c.user.id;
        const roleName = c.user.role === 'owner' ? '🏡 Dacha Egasi' : '👤 Mijoz';
        const lastMsg = c.last_message ? c.last_message.message : 'Yozishma yo\'q';

        return `
          <div class="conversation-item ${isActive ? 'active' : ''}" onclick="selectConversation(${c.user.id}, '${escapeHtml(c.user.name)}', '${escapeHtml(c.user.phone || '-')}', '${c.user.role}')">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">${escapeHtml(c.user.name)}</span>
              ${c.unread_count > 0 ? `<span style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 9999px; font-size: 0.7rem; font-weight: 800;">${c.unread_count} ta yangi</span>` : ''}
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">${roleName} | 📞 ${escapeHtml(c.user.phone || '-')}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.15rem;">
              ${escapeHtml(lastMsg)}
            </div>
          </div>
        `;
      }).join('');
    }

    async function selectConversation(userId, userName, userPhone, userRole) {
      activeChatUserId = userId;
      loadSupportConversations();

      const roleBadge = userRole === 'owner' ? '🏡 Dacha Egasi' : '👤 Mijoz';
      document.getElementById('activeChatUserInfo').innerHTML = `
        <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a;">${userName}</div>
        <div style="font-size: 0.8rem; color: #64748b;">${roleBadge} | 📞 ${userPhone}</div>
      `;

      document.getElementById('adminChatFooter').style.display = 'block';

      await loadChatMessages(userId, true);

      if (chatPollingTimer) clearInterval(chatPollingTimer);
      chatPollingTimer = setInterval(() => {
        if (activeChatUserId) {
          loadChatMessages(activeChatUserId, false);
        }
      }, 3500);
    }

    async function loadChatMessages(userId, scrollToBottom = false) {
      try {
        const res = await fetch(`${API_BASE}/admin/support/chats/${userId}`, {
          headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          const result = await res.json();
          renderAdminChatMessages(result.messages || [], scrollToBottom);
        }
      } catch (e) {
        console.error('loadChatMessages error:', e);
      }
    }

    function renderAdminChatMessages(messages, scrollToBottom = false) {
      const container = document.getElementById('adminChatBody');
      if (messages.length === 0) {
        container.innerHTML = `
          <div style="text-align: center; margin: auto; color: var(--text-muted); font-size: 0.9rem;">
            Ushbu foydalanuvchi bilan yozishmalar mavjud emas.
          </div>
        `;
        return;
      }

      container.innerHTML = messages.map(m => {
        const isUser = m.sender_type === 'user';
        const date = new Date(m.created_at);
        const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        return `
          <div class="${isUser ? 'admin-bubble-user' : 'admin-bubble-admin'}">
            <div style="font-size: 0.72rem; font-weight: 700; opacity: 0.8; margin-bottom: 0.2rem;">
              ${isUser ? '👤 Foydalanuvchi' : '🛡️ Siz (Admin)'}
            </div>
            <div>${escapeHtml(m.message)}</div>
            <div style="font-size: 0.68rem; text-align: right; opacity: 0.7; margin-top: 0.25rem;">
              ${timeStr}
            </div>
          </div>
        `;
      }).join('');

      if (scrollToBottom) {
        container.scrollTop = container.scrollHeight;
      }
    }

    async function handleAdminReply(e) {
      e.preventDefault();
      if (!activeChatUserId) return;

      const input = document.getElementById('adminReplyInput');
      const text = input.value.trim();
      if (!text) return;

      const btn = document.getElementById('adminReplyBtn');
      btn.disabled = true;
      input.value = '';

      try {
        const res = await fetch(`${API_BASE}/admin/support/chats/${activeChatUserId}/reply`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${adminToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ message: text })
        });

        if (res.ok) {
          await loadChatMessages(activeChatUserId, true);
          loadSupportConversations(true);
        } else {
          alert('Javob yuborishda xatolik yuz berdi');
        }
      } catch (err) {
        alert('Server bilan bog\'lanishda xatolik');
      } finally {
        btn.disabled = false;
        input.focus();
      }
    }

    function adminLogout() {
      localStorage.removeItem('oromgo_token');
      localStorage.removeItem('oromgo_user');
      window.location.href = '/';
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      })[m]);
    }
  </script>
</body>
</html>
