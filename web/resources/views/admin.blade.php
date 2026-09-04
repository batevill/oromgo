<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oromgo - Admin Boshqaruv & Murojaatlar Paneli</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="/css/admin.css?v={{ time() }}">
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

  <script src="/js/admin.js?v={{ time() }}"></script>
</body>
</html>
