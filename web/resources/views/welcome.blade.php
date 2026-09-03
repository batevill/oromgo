<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oromgo - Dachalarni ijaraga berish va bron qilish platformasi</title>
  <meta name="description" content="O'zbekistonning eng so'lim va qulay dachalari: Chorvoq, Bo'stonliq, Amirsoy va Zomin dachalari. Real vaqtda narx hisoblash va onlayn bron qilish.">
  
  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏡</text></svg>">

  <!-- Leaflet Map CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

  <!-- ==========================================
       NAVBAR
       ========================================== -->
  <nav class="navbar">
    <div class="nav-container">
      <a href="/" class="brand-logo">
        <div class="logo-icon">🏡</div>
        <span>Oromgo</span>
      </a>

      <div class="nav-actions">
        <!-- Support Chat Button for users -->
        <a href="/support" class="btn btn-outline" style="padding: 0.5rem 0.95rem; font-size: 0.875rem; font-weight: 700; border-color: #0d9488; color: #0d9488; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;">
          <span>💬</span> <span>Adminga murojaat</span>
        </a>

        <button class="btn btn-accent" onclick="openOwnerModal()">
          <span>➕</span> E'lon berish
        </button>

        <!-- Notification Bell Button -->
        <button class="btn btn-icon notification-bell-btn" id="notificationBellBtn" onclick="toggleNotificationsModal()" title="Bildirishnomalar">
          <span>🔔</span>
          <span class="notification-badge" id="unreadBadgeCount" style="display: none;">0</span>
        </button>

        <div id="navUserBox">
          <button class="btn btn-outline" onclick="openAuthModal()">Kirish</button>
        </div>
      </div>
    </div>
  </nav>

  <!-- ==========================================
       HERO & SEARCH SECTION
       ========================================== -->
  <header class="hero-section">
    <div class="hero-badge">
      <span>✨</span> O'zbekistonning №1 Dacha Platformasi
    </div>
    <h1 class="hero-title">
      Eng so'lim va shinam <span>dachalarni</span> toping
    </h1>
    <p class="hero-subtitle">
      Chorvoq, Bo'stonliq, Amirsoy va Zomin tog'laridagi eng yaxshi dachalarni onlayn tanlang, kalendarda bo'sh kunlarni ko'ring va qulay bron qiling.
    </p>

    <!-- Search Card -->
    <form id="searchForm" class="search-card">
      <div class="search-field">
        <label for="searchRegion">Viloyat / Hudud</label>
        <select id="searchRegion">
          <option value="">Barcha viloyatlar</option>
        </select>
      </div>

      <div class="search-field">
        <label for="searchDistrict">Tuman / Shahar</label>
        <select id="searchDistrict">
          <option value="">Barcha tumanlar</option>
        </select>
      </div>

      <div class="search-field">
        <label for="searchCapacity">Mehmonlar soni</label>
        <select id="searchCapacity">
          <option value="">Istalgan sig'im</option>
          <option value="4">4+ kishilik</option>
          <option value="8">8+ kishilik</option>
          <option value="12">12+ kishilik</option>
          <option value="16">16+ kishilik (Katta jamoa)</option>
        </select>
      </div>

      <div class="search-field">
        <label for="searchCurrency">Valyuta</label>
        <select id="searchCurrency">
          <option value="USD">USD ($)</option>
          <option value="UZS">UZS (so'm)</option>
        </select>
      </div>

      <div class="search-field">
        <label for="searchPrice">Maksimal narx</label>
        <input type="number" id="searchPrice" placeholder="Masalan: 200" min="0" />
      </div>

      <button type="submit" class="btn btn-primary btn-search">
        <span>🔍</span> Qidirish
      </button>
    </form>
  </header>

  <!-- ==========================================
       CATEGORY PILLS
       ========================================== -->
  <div class="category-bar">
    <button class="pill-btn active" onclick="filterByCategory(this, 'all')">🏡 Barcha dachalar</button>
    <button class="pill-btn" id="myDachasPillBtn" onclick="openOwnerCabinetModal('dachas')" style="display: none; color: #0284c7; border-color: #bae6fd; font-weight: 700;">🗂️ Mening dachalarim</button>
    <button class="pill-btn" onclick="filterByCategory(this, 'favorites')" style="color: #e11d48; border-color: #fecdd3;">❤️ Sevimlilarim</button>
    <button class="pill-btn" onclick="filterByCategory(this, 'pool')">🏊‍♂️ Basseynli</button>
    <button class="pill-btn" onclick="filterByCategory(this, 'sauna')">🧖‍♂️ Sauna / Hammom</button>
    <button class="pill-btn" onclick="filterByCategory(this, 'mountain')">🏔️ Tog' manzarasi</button>
    <button class="pill-btn" onclick="filterByCategory(this, 'billiard')">🎱 Bilyard</button>
    <button class="pill-btn" onclick="filterByCategory(this, 'playstation')">🎮 Playstation 5</button>
  </div>

  <!-- ==========================================
       MAIN CONTENT: DACHAS GRID & MAP VIEW
       ========================================== -->
  <main class="main-container">
    <div class="section-header">
      <h2 class="section-title" id="sectionTitle">Tavsiya etiladigan dachalar</h2>
      <span class="results-count" id="resultsCount">Yuklanmoqda...</span>
    </div>

    <!-- Grid View -->
    <div class="dacha-grid" id="dachaGrid">
      <!-- Dachas will be dynamically inserted here via app.js -->
    </div>

    <!-- Map View (Hidden by default or toggled) -->
    <div id="mapViewContainer" style="display: none; border-radius: var(--radius-xl); overflow: hidden; border: 1px solid var(--border); box-shadow: var(--glass-shadow);">
      <div id="mainMap" style="width: 100%; height: 650px;"></div>
    </div>
  </main>

  <!-- Floating Map Toggle Button (Airbnb Style) -->
  <div class="floating-map-btn-container">
    <button class="floating-map-btn" id="floatingMapBtn" onclick="toggleViewMode()">
      <span id="mapBtnIcon">🗺️</span> <span id="mapBtnText">Xaritani ko'rsatish</span>
    </button>
  </div>

  <!-- ==========================================
       MODAL: DACHA DETAIL & LIVE BOOKING
       ========================================== -->
  <div class="modal-backdrop" id="detailModal">
    <div class="modal-content" style="max-width: 960px;">
      <button class="modal-close" onclick="closeModal('detailModal')">✕</button>
      <div id="detailModalContent">
        <!-- Injected via JavaScript -->
      </div>
    </div>
  </div>

  <!-- ==========================================
       MODAL: OWNER CREATE DACHA
       ========================================== -->
  <div class="modal-backdrop" id="ownerModal">
    <div class="modal-content" style="max-width: 760px; padding: 2rem;">
      <button class="modal-close" onclick="closeModal('ownerModal')">✕</button>
      
      <div style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark);">🏡 Yangi dacha e'lonini joylash</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Dachangiz haqidagi barcha ma'lumotlarni to'ldiring va darhol mijozlar qabul qiling.</p>
      </div>

      <form id="createDachaForm">
        <div class="form-group">
          <label>Dacha nomi (Sarlavha) *</label>
          <input type="text" name="name" class="form-control" placeholder="Masalan: Chorvoq Panorama Lux Dacha" required />
        </div>

        <div class="form-group">
          <label>Tavsif</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Dacha sharoitlari, afzalliklari haqida batafsil yozing..."></textarea>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Viloyat *</label>
            <select name="region" id="ownerRegion" class="form-control" required>
              <option value="">Viloyatni tanlang</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tuman *</label>
            <select name="district" id="ownerDistrict" class="form-control" required>
              <option value="">Tumanni tanlang</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Mahalla / Hudud</label>
            <input type="text" name="mahalla" class="form-control" placeholder="Yusufxona" />
          </div>
          <div class="form-group">
            <label>Aniq manzil / Mo'ljal</label>
            <input type="text" name="address" class="form-control" placeholder="Soy bo'yi 12-uy" />
          </div>
        </div>

        <div class="form-row-3">
          <div class="form-group">
            <label>Ish kunlari narxi *</label>
            <input type="number" name="weekday_price" class="form-control" placeholder="150" required />
          </div>
          <div class="form-group">
            <label>Dam olish narxi</label>
            <input type="number" name="weekend_price" class="form-control" placeholder="200" />
          </div>
          <div class="form-group">
            <label>Valyuta *</label>
            <select name="currency" class="form-control">
              <option value="USD">USD ($)</option>
              <option value="UZS">UZS (so'm)</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Sig'imi (Kishi soni) *</label>
            <input type="number" name="capacity" class="form-control" value="10" min="1" required />
          </div>
          <div class="form-group">
            <label>Xonalar soni *</label>
            <input type="number" name="rooms_count" class="form-control" value="4" min="1" required />
          </div>
        </div>

        <div class="form-group">
          <label>Mavjud qulayliklar</label>
          <div id="amenitiesCheckboxes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
            <!-- Checkboxes injected via app.js -->
          </div>
        </div>

        <div class="form-group">
          <label>Dacha rasmlari (Bir nechta tanlang)</label>
          <input type="file" name="images[]" class="form-control" multiple accept="image/*" />
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.95rem; font-size: 1.05rem; margin-top: 1rem;">
          ✨ E'lonni joylash
        </button>
      </form>
    </div>
  </div>

  <!-- ==========================================
       MODAL: AUTH (TELEGRAM / GOOGLE)
       ========================================== -->
  <div class="modal-backdrop" id="authModal">
    <div class="modal-content" style="max-width: 440px; padding: 2.25rem; text-align: center;">
      <button class="modal-close" onclick="closeModal('authModal')">✕</button>

      <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔐</div>
      <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--dark); margin-bottom: 0.5rem;">Oromgo tizimiga kirish</h2>
      <p id="authNotice" style="color: #b45309; background: #fef3c7; padding: 0.6rem; border-radius: var(--radius-sm); font-size: 0.825rem; font-weight: 600; margin-bottom: 1.25rem; display: none;"></p>
      
      <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
        Dachalarni batafsil ko'rish, bron qilish yoki e'lon berish uchun profilingizga kiring:
      </p>

      <div style="display: flex; flex-direction: column; gap: 0.85rem;">
        <a href="/auth/telegram/redirect" class="btn" style="background: #229ED9; color: white; padding: 0.85rem; font-size: 0.95rem;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
          Telegram orqali kirish
        </a>

        <a href="/auth/google/redirect" class="btn btn-outline" style="padding: 0.85rem; font-size: 0.95rem;">
          <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
          Google orqali kirish
        </a>

        <div style="position: relative; margin: 0.75rem 0;">
          <hr style="border: none; border-top: 1px solid var(--border);" />
          <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: white; padding: 0 0.5rem; font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">YOKI TEST UCHUN</span>
        </div>

        <button class="btn btn-outline" onclick="loginAsDemo('owner')" style="background: var(--bg-page); border-style: dashed;">
          ⚡ Dacha Egasi (Alisher) sifatida sinab ko'rish
        </button>
        <button class="btn btn-outline" onclick="loginAsDemo('user')" style="background: var(--bg-page); border-style: dashed;">
          👤 Mijoz (Jasur) sifatida sinab ko'rish
        </button>
        <button class="btn btn-outline" onclick="loginAsDemo('admin')" style="background: #f5f3ff; border-color: #c4b5fd; color: #6d28d9; border-style: dashed; font-weight: 700;">
          🛡️ Admin Moderator sifatida sinab ko'rish
        </button>
      </div>
    </div>
  </div>

  <!-- ==========================================
       MODAL: NOTIFICATION CENTER (BILDIRISHNOMALAR)
       ========================================== -->
  <div class="modal-backdrop" id="notificationModal">
    <div class="modal-content notification-modal-content">
      <div class="notification-modal-header">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
          <div class="notification-header-icon">🔔</div>
          <div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0;">Bildirishnomalar markazi</h2>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Bronlar, tasdiqlashlar va Telegram bot integratsiyasi</p>
          </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <button class="btn-text-sm" onclick="markAllNotificationsAsRead()" title="Barchasini o'qilgan deb belgilash">
            ✓ Barchasini o'qish
          </button>
          <button class="modal-close" onclick="closeModal('notificationModal')">✕</button>
        </div>
      </div>

      <!-- Telegram Bot Link Promo Box -->
      <div class="telegram-link-card" id="telegramLinkCard">
        <div class="telegram-link-info">
          <div class="telegram-badge-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
          </div>
          <div>
            <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: #0c4a6e;">Telegram Botga ulanish</h4>
            <p style="font-size: 0.8rem; margin: 0.15rem 0 0; color: #0369a1;" id="telegramStatusText">
              Yangi bron so'rovlarini Telegramda ko'rish va <b>[Tasdiqlash]</b> tugmalarini bosish uchun botni ulang.
            </p>
          </div>
        </div>
        <button class="btn btn-telegram-sm" onclick="openTelegramBotLink()">
          <span>🚀 Botga ulanish</span>
        </button>
      </div>

      <!-- Notification Filter Tabs -->
      <div class="notification-tabs">
        <button class="notif-tab-btn active" onclick="filterNotifications('all', this)">Barchasi (<span id="notifTotalCount">0</span>)</button>
        <button class="notif-tab-btn" onclick="filterNotifications('unread', this)">O'qilmagan (<span id="notifUnreadCount">0</span>)</button>
        <button class="notif-tab-btn" onclick="filterNotifications('bookings', this)">Bron so'rovlari</button>
      </div>

      <!-- Notifications List Body -->
      <div class="notification-list-body" id="notificationList">
        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
          <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📭</div>
          <p>Yuklanmoqda...</p>
        </div>
      </div>
    </div>
  <!-- ==========================================
       MODAL: OWNER DASHBOARD & CABINET
       ========================================== -->
  <div class="modal-backdrop" id="ownerCabinetModal">
    <div class="modal-content cabinet-modal-content">
      <div class="cabinet-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <div style="font-size: 2rem;">🗂️</div>
          <div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0;">Dacha Egasi Kabineti</h2>
            <p style="font-size: 0.825rem; color: var(--text-muted); margin: 0;">Joylagan dachalaringiz, tushgan bronlar va kalendarni boshqaring</p>
          </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <button class="btn btn-accent" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="closeModal('ownerCabinetModal'); openOwnerModal();">
            <span>➕</span> Yangi e'lon
          </button>
          <button class="modal-close" onclick="closeModal('ownerCabinetModal')">✕</button>
        </div>
      </div>

      <!-- Cabinet Tabs -->
      <div class="cabinet-tabs">
        <button class="cabinet-tab-btn active" id="tabOwnerDachasBtn" onclick="switchCabinetTab('dachas')">
          🏡 Mening dachalarim (<span id="ownerDachasCount">0</span>)
        </button>
        <button class="cabinet-tab-btn" id="tabOwnerBookingsBtn" onclick="switchCabinetTab('bookings')">
          📋 Bronlar (<span id="ownerBookingsCount">0</span>)
        </button>
        <button class="cabinet-tab-btn" id="tabOwnerReportsBtn" onclick="switchCabinetTab('reports')">
          📊 Hisobotlar & Daromad
        </button>
        <button class="cabinet-tab-btn" id="tabOwnerBlockDatesBtn" onclick="switchCabinetTab('blockDates')">
          ➕ Tashqi bron / Yopish
        </button>
      </div>

      <!-- Cabinet Body -->
      <div class="cabinet-body">
        <!-- Tab 1: Dachalarim -->
        <div id="cabinetTabDachas">
          <div id="ownerDachasList">
            <!-- Injected via JS -->
          </div>
        </div>

        <!-- Tab 2: Bronlar -->
        <div id="cabinetTabBookings" style="display: none;">
          <div id="ownerBookingsList">
            <!-- Injected via JS -->
          </div>
        </div>

        <!-- Tab 3: Hisobotlar va Daromad statistikasi -->
        <div id="cabinetTabReports" style="display: none;">
          <div id="ownerReportsContainer">
            <!-- Injected via JS -->
          </div>
        </div>

        <!-- Tab 4: Tashqi bron / Sanalarni yopish formasi -->
        <div id="cabinetTabBlockDates" style="display: none;">
          <div style="max-width: 620px; margin: 0 auto; background: white; padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--dark);">➕ Tashqi bron kiritish / Sanalarni yopish</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem;">
              Telegram yoki telefon orqali band qilingan dachani kiritib, daromad hisobotingizni to'liq va to'g'ri yuriting.
            </p>
            
            <form id="ownerBlockDatesForm">
              <div class="form-group">
                <label style="font-weight: 700; font-size: 0.85rem;">Bron manbasi *</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-top: 0.25rem;">
                  <label class="source-radio-label">
                    <input type="radio" name="manualSource" value="telegram" checked onchange="handleManualSourceChange()" />
                    <span>📱 Telegram</span>
                  </label>
                  <label class="source-radio-label">
                    <input type="radio" name="manualSource" value="phone" onchange="handleManualSourceChange()" />
                    <span>📞 Telefon</span>
                  </label>
                  <label class="source-radio-label">
                    <input type="radio" name="manualSource" value="manual" onchange="handleManualSourceChange()" />
                    <span>🚫 Yopish / Ta'mir</span>
                  </label>
                </div>
              </div>

              <div class="form-group">
                <label style="font-weight: 700; font-size: 0.85rem;">Dachani tanlang *</label>
                <select id="blockDatesDachaSelect" class="form-control" required>
                  <!-- Injected via JS -->
                </select>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                  <label style="font-weight: 700; font-size: 0.85rem;">Boshlanish sanasi *</label>
                  <input type="date" id="blockStartDate" class="form-control" required />
                </div>
                <div class="form-group">
                  <label style="font-weight: 700; font-size: 0.85rem;">Tugash sanasi *</label>
                  <input type="date" id="blockEndDate" class="form-control" required />
                </div>
              </div>

              <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div class="form-group">
                  <label style="font-weight: 700; font-size: 0.85rem;">Kelishilgan narx</label>
                  <input type="number" id="blockPrice" class="form-control" placeholder="Masalan: 1500000" min="0" />
                </div>
                <div class="form-group">
                  <label style="font-weight: 700; font-size: 0.85rem;">Valyuta</label>
                  <select id="blockCurrency" class="form-control">
                    <option value="USD">USD ($)</option>
                    <option value="UZS">UZS (so'm)</option>
                  </select>
                </div>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                  <label style="font-weight: 700; font-size: 0.85rem;">Mijoz ismi (Ixtiyoriy)</label>
                  <input type="text" id="blockCustomerName" class="form-control" placeholder="Masalan: Jasur" />
                </div>
                <div class="form-group">
                  <label style="font-weight: 700; font-size: 0.85rem;">Telefon raqam</label>
                  <input type="text" id="blockCustomerPhone" class="form-control" placeholder="+998901234567" />
                </div>
              </div>

              <div class="form-group">
                <label style="font-weight: 700; font-size: 0.85rem;">Izoh / Sabab</label>
                <input type="text" id="blockReason" class="form-control" placeholder="Masalan: Telegram orqali bron qilindi" />
              </div>

              <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; margin-top: 0.5rem;">
                💾 Saqlash va Hisobotga kiritish
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================
       MODAL: OWNER EDIT DACHA
       ========================================== -->
  <div class="modal-backdrop" id="editDachaModal">
    <div class="modal-content" style="max-width: 760px; padding: 2rem;">
      <button class="modal-close" onclick="closeModal('editDachaModal')">✕</button>
      
      <div style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
        <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--dark);">✏️ Dachani tahrirlash</h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Dacha ma'lumotlari, narxlari va qulayliklarini yangilang.</p>
      </div>

      <form id="editDachaForm">
        <input type="hidden" id="editDachaId" />

        <div class="form-group">
          <label>Dacha nomi (Sarlavha) *</label>
          <input type="text" id="editName" class="form-control" required />
        </div>

        <div class="form-group">
          <label>Tavsif</label>
          <textarea id="editDescription" class="form-control" rows="3"></textarea>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Viloyat *</label>
            <select id="editRegion" class="form-control" required>
              <option value="">Viloyatni tanlang</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tuman *</label>
            <select id="editDistrict" class="form-control" required>
              <option value="">Tumanni tanlang</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Mahalla / Hudud</label>
            <input type="text" id="editMahalla" class="form-control" />
          </div>
          <div class="form-group">
            <label>Aniq manzil / Mo'ljal</label>
            <input type="text" id="editAddress" class="form-control" />
          </div>
        </div>

        <div class="form-row-3">
          <div class="form-group">
            <label>Ish kunlari narxi *</label>
            <input type="number" id="editWeekdayPrice" class="form-control" required />
          </div>
          <div class="form-group">
            <label>Dam olish narxi</label>
            <input type="number" id="editWeekendPrice" class="form-control" />
          </div>
          <div class="form-group">
            <label>Valyuta *</label>
            <select id="editCurrency" class="form-control">
              <option value="USD">USD ($)</option>
              <option value="UZS">UZS (so'm)</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Sig'imi (Kishi soni) *</label>
            <input type="number" id="editCapacity" class="form-control" min="1" required />
          </div>
          <div class="form-group">
            <label>Xonalar soni *</label>
            <input type="number" id="editRoomsCount" class="form-control" min="1" required />
          </div>
        </div>

        <div class="form-group">
          <label>Mavjud qulayliklar</label>
          <div id="editAmenitiesCheckboxes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
            <!-- Checkboxes injected via JS -->
          </div>
        </div>

        <div class="form-group">
          <label>Yangi rasmlar qo'shish (Ixtiyoriy)</label>
          <input type="file" id="editImages" class="form-control" multiple accept="image/*" />
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.95rem; font-size: 1.05rem; margin-top: 1rem;">
          💾 Saqlash va yangilash
        </button>
      </form>
    </div>
  </div>


  <!-- ==========================================
       MODAL: ADMIN MODERATION PANEL
       ========================================== -->
  <div class="modal-backdrop" id="adminModal">
    <div class="modal-content admin-modal-content" style="max-width: 1080px; padding: 0;">
      <div style="padding: 1.5rem 1.75rem 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: white; border-top-left-radius: var(--radius-xl); border-top-right-radius: var(--radius-xl);">
        <div>
          <div style="display: flex; align-items: center; gap: 0.6rem;">
            <span style="font-size: 1.5rem;">🛡️</span>
            <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--dark); margin: 0;">Admin Moderatsiya Paneli</h2>
          </div>
          <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
            Dacha e'lonlarini tekshirish, tasdiqlash (faol qilish) va nofaol holatga o'tkazish.
          </p>
        </div>
        <button class="modal-close" style="position: static;" onclick="closeModal('adminModal')">✕</button>
      </div>

      <div style="padding: 1.25rem 1.75rem; background: var(--bg-page); max-height: 75vh; overflow-y: auto;">
        <!-- Admin Stats Bar -->
        <div class="admin-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
          <div class="admin-stat-card" style="background: white; padding: 1rem 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border); border-left: 4px solid var(--primary);">
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Jami E'lonlar</span>
            <h3 id="adminStatTotal" style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin-top: 0.25rem;">0</h3>
          </div>
          <div class="admin-stat-card" style="background: white; padding: 1rem 1.25rem; border-radius: var(--radius-md); border: 1px solid #fef08a; border-left: 4px solid #eab308;">
            <span style="font-size: 0.8rem; color: #a16207; font-weight: 700; text-transform: uppercase;">⏳ Moderatsiyada</span>
            <h3 id="adminStatPending" style="font-size: 1.6rem; font-weight: 800; color: #a16207; margin-top: 0.25rem;">0</h3>
          </div>
          <div class="admin-stat-card" style="background: white; padding: 1rem 1.25rem; border-radius: var(--radius-md); border: 1px solid #bbf7d0; border-left: 4px solid #22c55e;">
            <span style="font-size: 0.8rem; color: #15803d; font-weight: 700; text-transform: uppercase;">🟢 Faol (Saytda)</span>
            <h3 id="adminStatActive" style="font-size: 1.6rem; font-weight: 800; color: #15803d; margin-top: 0.25rem;">0</h3>
          </div>
          <div class="admin-stat-card" style="background: white; padding: 1rem 1.25rem; border-radius: var(--radius-md); border: 1px solid #fecdd3; border-left: 4px solid #ef4444;">
            <span style="font-size: 0.8rem; color: #b91c1c; font-weight: 700; text-transform: uppercase;">⏸️ Nofaol / To'xtatilgan</span>
            <h3 id="adminStatInactive" style="font-size: 1.6rem; font-weight: 800; color: #b91c1c; margin-top: 0.25rem;">0</h3>
          </div>
        </div>

        <!-- Filter and Search Bar -->
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;" id="adminFilterTabs">
            <button class="pill-btn active" onclick="filterAdminDachas(this, 'all')">Barcha e'lonlar</button>
            <button class="pill-btn" onclick="filterAdminDachas(this, 'pending')" style="color: #a16207; border-color: #fef08a;">⏳ Moderatsiyada</button>
            <button class="pill-btn" onclick="filterAdminDachas(this, 'active')" style="color: #15803d; border-color: #bbf7d0;">🟢 Faol dachalar</button>
            <button class="pill-btn" onclick="filterAdminDachas(this, 'inactive')" style="color: #b91c1c; border-color: #fecdd3;">⏸️ Nofaol</button>
          </div>

          <div style="min-width: 260px;">
            <input type="text" id="adminSearchInput" class="form-control" placeholder="🔍 Dacha yoki egasini qidirish..." oninput="debounceAdminSearch()" style="padding: 0.5rem 0.85rem; font-size: 0.85rem;" />
          </div>
        </div>

        <!-- Dachas List Container -->
        <div id="adminDachasList" style="display: flex; flex-direction: column; gap: 1rem;">
          <!-- Injected via JavaScript -->
        </div>
      </div>
    </div>
  </div>

  <footer style="background: white; border-top: 1px solid var(--border); padding: 3rem 1.5rem 2rem; margin-top: 4rem;">
    <div style="max-width: 1380px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1.5rem;">
      <a href="/" class="brand-logo">
        <div class="logo-icon">🏡</div>
        <span>Oromgo</span>
      </a>
      <p style="color: var(--text-muted); font-size: 0.9rem;">
        © 2026 Oromgo. Barcha huquqlar himoyalangan. Dachalarni qulay va shaffof ijaraga berish platformasi.
      </p>
    </div>
  </footer>

  <!-- Leaflet Map JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <!-- App Client Scripts -->
  <script src="/js/app.js"></script>
</body>
</html>
