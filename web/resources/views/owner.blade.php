<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oromgo - Dacha Egasi Boshqaruv & Hisobot Paneli</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏡</text></svg>">
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .owner-layout {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: #f8fafc;
    }
    .owner-navbar {
      background: #0f172a;
      color: white;
      padding: 0.85rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .owner-brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 1.25rem;
      font-weight: 800;
      color: white;
      text-decoration: none;
    }
    .owner-brand .badge {
      background: #0284c7;
      color: white;
      font-size: 0.75rem;
      padding: 0.2rem 0.6rem;
      border-radius: 9999px;
      font-weight: 700;
    }
    .owner-main {
      max-width: 1400px;
      width: 100%;
      margin: 1.5rem auto 3rem;
      padding: 0 1.5rem;
      flex: 1;
    }
    .owner-nav-tabs {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
      border-bottom: 2px solid var(--border);
      padding-bottom: 0.5rem;
      overflow-x: auto;
      flex-wrap: nowrap;
    }
    .owner-nav-tab {
      padding: 0.75rem 1.25rem;
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
      white-space: nowrap;
      transition: var(--transition-fast);
    }
    .owner-nav-tab.active {
      background: white;
      color: var(--primary);
      box-shadow: var(--shadow-sm);
    }
    .owner-tab-pane {
      display: none;
    }
    .owner-tab-pane.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .owner-stat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.25rem;
      margin-bottom: 1.5rem;
    }
    .owner-stat-card {
      background: white;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      box-shadow: var(--shadow-sm);
      transition: transform 0.2s ease;
    }
    .owner-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }
    .owner-stat-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--text-muted);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .owner-stat-value {
      font-size: 1.85rem;
      font-weight: 900;
      color: var(--dark);
    }
    .owner-stat-sub {
      font-size: 0.8rem;
      color: var(--text-muted);
      font-weight: 600;
    }
    .source-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 1.25rem;
      background: #f8fafc;
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      margin-bottom: 0.75rem;
      transition: background 0.2s;
    }
    .source-item:hover {
      background: #f1f5f9;
    }
    .owner-dacha-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 1.5rem;
    }
    .owner-booking-card {
      background: white;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      margin-bottom: 1.25rem;
      box-shadow: var(--shadow-sm);
    }
    .owner-booking-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .owner-booking-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 0.85rem;
      background: #f8fafc;
      padding: 1rem;
      border-radius: var(--radius-md);
      font-size: 0.9rem;
    }
  </style>
</head>
<body class="owner-layout">

  <!-- Navbar -->
  <header class="owner-navbar">
    <div style="display: flex; align-items: center; gap: 1.25rem;">
      <a href="/" class="owner-brand">
        <span>🏡 Oromgo</span>
        <span class="badge">Dacha Egasi Kabineti</span>
      </a>
      <a href="/" style="color: #94a3b8; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" title="Bosh sahifaga qaytish">
        <span>🌐</span> <span>Asosiy sayt</span>
      </a>
    </div>

    <div style="display: flex; align-items: center; gap: 0.85rem;">
      <button class="btn btn-accent" style="padding: 0.5rem 1rem; font-size: 0.875rem;" onclick="openCreateDachaModal()">
        <span>➕</span> Yangi e'lon
      </button>

      <div id="ownerUserBox" style="display: flex; align-items: center; gap: 0.6rem; background: rgba(255,255,255,0.08); padding: 0.4rem 0.85rem; border-radius: var(--radius-md);">
        <span style="font-size: 0.9rem; font-weight: 700;" id="ownerUserName">Alisher Rahimov</span>
        <button class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; color: #f87171; border-color: rgba(248,113,113,0.3);" onclick="logoutOwner()">Chiqish</button>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="owner-main">

    <!-- Navigation Tabs -->
    <div class="owner-nav-tabs">
      <button class="owner-nav-tab active" onclick="switchOwnerTab('reports', this)">
        <span>📊</span> Hisobotlar & Daromad
      </button>
      <button class="owner-nav-tab" onclick="switchOwnerTab('dachas', this)">
        <span>🏡</span> Mening dachalarim (<span id="ownerDachasCountBadge">0</span>)
      </button>
      <button class="owner-nav-tab" onclick="switchOwnerTab('bookings', this)">
        <span>📋</span> Kelgan bronlar (<span id="ownerBookingsCountBadge">0</span>)
      </button>
      <button class="owner-nav-tab" onclick="switchOwnerTab('manualBooking', this)">
        <span>➕</span> Tashqi bron kiritish / Sanalarni yopish
      </button>
    </div>

    <!-- ==========================================
         TAB 1: HISOBOTLAR & DAROMAD (REPORTS)
         ========================================== -->
    <div id="ownerTabReports" class="owner-tab-pane active">
      <!-- Period Filter & Header -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; background: white; padding: 1.25rem 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        <div>
          <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0;">📊 Moliyaviy va Bandlik Hisoboti</h2>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0.25rem 0 0;">Davr: <strong id="reportPeriodLabel">-</strong></p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Davrni tanlang:</label>
          <select id="reportPeriodSelect" class="form-control" style="width: auto; padding: 0.5rem 1rem; font-size: 0.9rem; font-weight: 700;" onchange="loadReportsData(this.value)">
            <option value="this_month" selected>Shu oy</option>
            <option value="last_month">O'tgan oy</option>
            <option value="this_year">Shu yil</option>
            <option value="all">Barcha vaqt</option>
          </select>
        </div>
      </div>

      <!-- Stat Cards Grid -->
      <div class="owner-stat-grid" id="reportStatsGrid">
        <div class="owner-stat-card" style="border-left: 5px solid #10b981;">
          <div class="owner-stat-title">
            <span>💰 Jami daromad (USD)</span>
            <span style="font-size: 1.2rem;">💵</span>
          </div>
          <div class="owner-stat-value" id="statIncomeUsd" style="color: #10b981;">$0</div>
          <div class="owner-stat-sub">Tasdiqlangan bronlar bo'yicha</div>
        </div>

        <div class="owner-stat-card" style="border-left: 5px solid #3b82f6;">
          <div class="owner-stat-title">
            <span>💳 Jami daromad (UZS)</span>
            <span style="font-size: 1.2rem;">💳</span>
          </div>
          <div class="owner-stat-value" id="statIncomeUzs" style="color: #3b82f6;">0 so'm</div>
          <div class="owner-stat-sub">So'mda kelishilgan bronlar</div>
        </div>

        <div class="owner-stat-card" style="border-left: 5px solid #f59e0b;">
          <div class="owner-stat-title">
            <span>📅 Band kunlar soni</span>
            <span style="font-size: 1.2rem;">🗓️</span>
          </div>
          <div class="owner-stat-value" id="statBookedDays" style="color: #f59e0b;">0 kun</div>
          <div class="owner-stat-sub" id="statOccupancyRate">Bandlik darajasi: 0%</div>
        </div>

        <div class="owner-stat-card" style="border-left: 5px solid #8b5cf6;">
          <div class="owner-stat-title">
            <span>📋 Tasdiqlangan bronlar</span>
            <span style="font-size: 1.2rem;">✅</span>
          </div>
          <div class="owner-stat-value" id="statConfirmedBookings" style="color: #8b5cf6;">0 ta</div>
          <div class="owner-stat-sub" id="statTotalBookings">Jami so'rovlar: 0 ta</div>
        </div>
      </div>

      <!-- Sources Breakdown & Monthly Trend -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Sources -->
        <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
          <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin: 0 0 0.25rem;">📱 Bronlar va Daromad Manbalari</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 1.25rem;">Telegram yoki dastur orqali qancha daromad qilganingiz tahlili:</p>
          <div id="reportSourcesList">
            <!-- Injected via JS -->
          </div>
        </div>

        <!-- Monthly Trend -->
        <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
          <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin: 0 0 0.25rem;">📈 Oylik Daromad Dinamikasi</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 1.25rem;">So'nggi oylardagi tushumlar o'sishi:</p>
          <div id="reportMonthlyList">
            <!-- Injected via JS -->
          </div>
        </div>
      </div>

      <!-- Dachas Breakdown -->
      <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin: 0 0 0.75rem;">🏡 Dachalar Kesimida Daromad</h3>
        <div id="reportDachasList" style="display: flex; flex-direction: column; gap: 0.75rem;">
          <!-- Injected via JS -->
        </div>
      </div>
    </div>

    <!-- ==========================================
         TAB 2: MENING DACHALARIM
         ========================================== -->
    <div id="ownerTabDachas" class="owner-tab-pane">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0;">🏡 Mening Dacha E'lonlarim</h2>
        <button class="btn btn-primary" onclick="openCreateDachaModal()">
          <span>➕</span> Yangi e'lon joylash
        </button>
      </div>
      <div id="ownerDachasListContainer">
        <!-- Injected via JS -->
      </div>
    </div>

    <!-- ==========================================
         TAB 3: BRONLAR
         ========================================== -->
    <div id="ownerTabBookings" class="owner-tab-pane">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0;">📋 Kelgan Bronlar Ro'yxati</h2>
        <div style="display: flex; gap: 0.5rem;">
          <select id="bookingStatusFilter" class="form-control" style="width: auto;" onchange="loadBookingsData()">
            <option value="">Barcha holatlar</option>
            <option value="pending">Kutilmoqda ⏳</option>
            <option value="confirmed">Tasdiqlangan ✅</option>
            <option value="cancelled">Bekor qilingan ❌</option>
          </select>
          <select id="bookingSourceFilter" class="form-control" style="width: auto;" onchange="loadBookingsData()">
            <option value="">Barcha manbalar</option>
            <option value="telegram">Telegram 📱</option>
            <option value="app">Oromgo 🌟</option>
            <option value="phone">Telefon 📞</option>
            <option value="manual">Yopilgan 🚫</option>
          </select>
        </div>
      </div>
      <div id="ownerBookingsListContainer">
        <!-- Injected via JS -->
      </div>
    </div>

    <!-- ==========================================
         TAB 4: TASHQI BRON KIRITISH & YOPISH
         ========================================== -->
    <div id="ownerTabManualBooking" class="owner-tab-pane">
      <div style="max-width: 680px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: var(--radius-xl); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        <h2 style="font-size: 1.45rem; font-weight: 800; margin: 0 0 0.35rem; color: var(--dark);">➕ Tashqi bron kiritish / Sanalarni yopish</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 1.5rem;">
          Telegram yoki telefon orqali band qilingan dachani kiritib, daromad hisobotingizni 100% to'g'ri yuriting.
        </p>

        <form id="ownerManualBookingForm" onsubmit="handleManualBookingSubmit(event)">
          <div class="form-group">
            <label style="font-weight: 700; font-size: 0.9rem;">Bron manbasi *</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-top: 0.35rem;">
              <label class="source-radio-label">
                <input type="radio" name="manualBookingSource" value="telegram" checked onchange="updateManualFormPlaceholders()" />
                <span>📱 Telegram</span>
              </label>
              <label class="source-radio-label">
                <input type="radio" name="manualBookingSource" value="phone" onchange="updateManualFormPlaceholders()" />
                <span>📞 Telefon</span>
              </label>
              <label class="source-radio-label">
                <input type="radio" name="manualBookingSource" value="manual" onchange="updateManualFormPlaceholders()" />
                <span>🚫 Yopish / Ta'mir</span>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label style="font-weight: 700; font-size: 0.9rem;">Dachani tanlang *</label>
            <select id="manualDachaSelect" class="form-control" required>
              <!-- Injected via JS -->
            </select>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label style="font-weight: 700; font-size: 0.9rem;">Boshlanish sanasi *</label>
              <input type="date" id="manualStartDate" class="form-control" required />
            </div>
            <div class="form-group">
              <label style="font-weight: 700; font-size: 0.9rem;">Tugash sanasi *</label>
              <input type="date" id="manualEndDate" class="form-control" required />
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label style="font-weight: 700; font-size: 0.9rem;">Kelishilgan ijara narxi</label>
              <input type="number" id="manualPrice" class="form-control" placeholder="Masalan: 1500000" min="0" />
            </div>
            <div class="form-group">
              <label style="font-weight: 700; font-size: 0.9rem;">Valyuta</label>
              <select id="manualCurrency" class="form-control">
                <option value="USD">USD ($)</option>
                <option value="UZS">UZS (so'm)</option>
              </select>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label style="font-weight: 700; font-size: 0.9rem;">Mijoz ismi (Ixtiyoriy)</label>
              <input type="text" id="manualCustomerName" class="form-control" placeholder="Masalan: Jasur" />
            </div>
            <div class="form-group">
              <label style="font-weight: 700; font-size: 0.9rem;">Telefon raqam</label>
              <input type="text" id="manualCustomerPhone" class="form-control" placeholder="+998901234567" />
            </div>
          </div>

          <div class="form-group">
            <label style="font-weight: 700; font-size: 0.9rem;">Izoh / Qo'shimcha ma'lumot</label>
            <input type="text" id="manualNotes" class="form-control" placeholder="Telegram guruh orqali kelishildi" />
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem; font-weight: 800; margin-top: 0.75rem;">
            💾 Saqlash va Hisobotga kiritish
          </button>
        </form>
      </div>
    </div>

  </main>

  <!-- ==========================================
       MODAL: CREATE / EDIT DACHA
       ========================================== -->
  <div class="modal-backdrop" id="dachaFormModal">
    <div class="modal-content" style="max-width: 800px; padding: 2rem;">
      <button class="modal-close" onclick="closeModal('dachaFormModal')">✕</button>
      <div style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
        <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--dark);" id="dachaModalTitle">🏡 Yangi dacha e'lonini joylash</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem;" id="dachaModalSubtitle">Dachangiz haqidagi ma'lumotlarni to'ldiring</p>
      </div>

      <form id="ownerDachaForm" onsubmit="handleSaveDacha(event)">
        <input type="hidden" id="editDachaId" value="" />
        <div class="form-group">
          <label>Dacha nomi *</label>
          <input type="text" id="formDachaName" class="form-control" placeholder="Masalan: Chorvoq Panorama Lux" required />
        </div>

        <div class="form-group">
          <label>Tavsif</label>
          <textarea id="formDachaDescription" class="form-control" rows="3" placeholder="Dacha sharoitlari va afzalliklari..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label>Viloyat *</label>
            <select id="formDachaRegion" class="form-control" required onchange="handleRegionChange()">
              <option value="">Tanlang</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tuman / Hudud *</label>
            <select id="formDachaDistrict" class="form-control" required>
              <option value="">Tanlang</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label>Sig'imi (kishi) *</label>
            <input type="number" id="formDachaCapacity" class="form-control" min="1" value="10" required />
          </div>
          <div class="form-group">
            <label>Xonalar soni *</label>
            <input type="number" id="formDachaRooms" class="form-control" min="1" value="4" required />
          </div>
          <div class="form-group">
            <label>Valyuta</label>
            <select id="formDachaCurrency" class="form-control">
              <option value="USD">USD ($)</option>
              <option value="UZS">UZS (so'm)</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label>Ish kunlari narxi *</label>
            <input type="number" id="formDachaWeekdayPrice" class="form-control" placeholder="Masalan: 120" required />
          </div>
          <div class="form-group">
            <label>Dam olish kunlari narxi *</label>
            <input type="number" id="formDachaWeekendPrice" class="form-control" placeholder="Masalan: 150" required />
          </div>
        </div>

        <div id="formExistingMediaContainer" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;"></div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label>Rasmlar (Yangi qo'shish, max: 7 ta)</label>
            <input type="file" id="formDachaImages" class="form-control" accept="image/*" multiple />
          </div>
          <div class="form-group">
            <label>Video (Yangi qo'shish, 1 ta)</label>
            <input type="file" id="formDachaVideo" class="form-control" accept="video/*" />
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.9rem; font-size: 1rem; font-weight: 800; margin-top: 1rem;">
          💾 Saqlash
        </button>
      </form>
    </div>
  </div>

  <!-- ==========================================
       MODAL: DACHA TAFSILOTLARI (VIEW MODAL)
       ========================================== -->
  <div class="modal-backdrop" id="dachaDetailModal">
    <div class="modal-content" style="max-width: 900px; padding: 0; overflow: hidden; border-radius: var(--radius-xl);">
      <div style="position: relative; height: 260px; background: #0f172a;">
        <img id="detailCoverImg" src="" style="width: 100%; height: 100%; object-fit: cover;" />
        <button class="modal-close" style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.6); color: white; border-radius: 50%;" onclick="closeModal('dachaDetailModal')">✕</button>
        <div style="position: absolute; bottom: 15px; left: 20px; color: white;">
          <span id="detailStatusBadge" style="margin-bottom: 6px; display: inline-block;"></span>
          <h2 id="detailDachaTitle" style="font-size: 1.6rem; font-weight: 900; margin: 0; text-shadow: 0 2px 8px rgba(0,0,0,0.6);">Dacha Nomi</h2>
          <p id="detailDachaLocation" style="font-size: 0.9rem; margin: 0.2rem 0 0; opacity: 0.9;">📍 Viloyat, Tuman</p>
        </div>
      </div>

      <div style="padding: 1.75rem; max-height: calc(85vh - 260px); overflow-y: auto;">
        <!-- Quick stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
          <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; font-weight: 700;">Ish kunlari:</span>
            <strong id="detailWeekdayPrice" style="font-size: 1.2rem; color: var(--dark);">-</strong>
          </div>
          <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; font-weight: 700;">Dam olish kunlari:</span>
            <strong id="detailWeekendPrice" style="font-size: 1.2rem; color: var(--accent);">-</strong>
          </div>
          <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; font-weight: 700;">Sig'imi va Xonalar:</span>
            <strong id="detailCapacityRooms" style="font-size: 1.05rem; color: var(--dark);">-</strong>
          </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 1.5rem;">
          <h4 style="font-size: 1rem; font-weight: 800; color: var(--dark); margin: 0 0 0.4rem;">📝 Tavsif va Sharoitlar</h4>
          <p id="detailDescription" style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0; background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border);">-</p>
        </div>

        <!-- Bookings for this dacha -->
        <div style="margin-bottom: 1.5rem;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <h4 style="font-size: 1rem; font-weight: 800; color: var(--dark); margin: 0;">📅 Ushbu dachaning bandlik kalendari va bronlari</h4>
            <button class="btn btn-outline" id="detailAddManualBookingBtn" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; color: #0284c7; border-color: #bae6fd;">
              ➕ Tashqi bron kiritish
            </button>
          </div>
          <div id="detailDachaBookingsList">
            <!-- Injected via JS -->
          </div>
        </div>

        <!-- Action buttons in footer -->
        <div style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--border);">
          <button class="btn btn-primary" id="detailEditBtn" style="flex: 1; padding: 0.75rem; font-weight: 700;">
            ✏️ Ushbu dachani tahrirlash
          </button>
          <button class="btn btn-outline" onclick="closeModal('dachaDetailModal')" style="padding: 0.75rem 1.5rem;">
            Yopish
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="toastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>

  <!-- JS Logic -->
  <script>
    const API_BASE = '/api';
    let token = localStorage.getItem('oromgo_token') || '';
    let user = JSON.parse(localStorage.getItem('oromgo_user') || 'null');
    let ownerDachas = [];
    let ownerBookings = [];
    let locationsData = {};

    document.addEventListener('DOMContentLoaded', async () => {
      // Check auth
      if (!token || !user || (user.role !== 'owner' && user.role !== 'admin' && user.role !== 'super_admin')) {
        // Auto demo login as owner if not logged in
        await autoDemoLogin();
      } else {
        document.getElementById('ownerUserName').textContent = user.name;
      }

      await loadLocations();
      await Promise.all([
        loadReportsData('this_month'),
        loadDachasData(),
        loadBookingsData()
      ]);
    });

    async function autoDemoLogin() {
      try {
        const res = await fetch(`${API_BASE}/demo-login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ role: 'owner' })
        });
        if (res.ok) {
          const data = await res.json();
          token = data.token;
          user = data.user;
          localStorage.setItem('oromgo_token', token);
          localStorage.setItem('oromgo_user', JSON.stringify(user));
          document.getElementById('ownerUserName').textContent = user.name;
        }
      } catch (e) {
        console.error('Demo login error:', e);
      }
    }

    function switchOwnerTab(tabName, btnEl) {
      document.querySelectorAll('.owner-nav-tab').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.owner-tab-pane').forEach(el => el.classList.remove('active'));

      if (btnEl) btnEl.classList.add('active');

      const targetPane = document.getElementById('ownerTab' + tabName.charAt(0).toUpperCase() + tabName.slice(1));
      if (targetPane) targetPane.classList.add('active');

      if (tabName === 'reports') loadReportsData(document.getElementById('reportPeriodSelect').value);
      if (tabName === 'dachas') loadDachasData();
      if (tabName === 'bookings') loadBookingsData();
    }

    // ==========================================
    // REPORTS DATA
    // ==========================================
    async function loadReportsData(period = 'this_month') {
      try {
        const res = await fetch(`${API_BASE}/owner/reports?period=${period}`, {
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });

        if (res.ok) {
          const data = await res.json();
          renderReports(data);
        }
      } catch (err) {
        console.error('loadReportsData error:', err);
      }
    }

    function renderReports(rep) {
      document.getElementById('reportPeriodLabel').textContent = rep.period_label || '-';
      const sum = rep.summary || {};

      document.getElementById('statIncomeUsd').textContent = `$${(sum.total_income_usd || 0).toLocaleString()}`;
      document.getElementById('statIncomeUzs').textContent = `${(sum.total_income_uzs || 0).toLocaleString()} so'm`;
      document.getElementById('statBookedDays').textContent = `${sum.total_booked_days || 0} kun`;
      document.getElementById('statOccupancyRate').textContent = `Bandlik darajasi: ${sum.occupancy_rate || 0}%`;
      document.getElementById('statConfirmedBookings').textContent = `${sum.confirmed_bookings || 0} ta`;
      document.getElementById('statTotalBookings').textContent = `Jami so'rovlar: ${sum.total_bookings || 0} ta`;

      // Sources
      const sourcesContainer = document.getElementById('reportSourcesList');
      sourcesContainer.innerHTML = (rep.sources || []).map(s => {
        const incomeText = s.income_usd > 0 ? `$${s.income_usd.toLocaleString()}` : (s.income_uzs > 0 ? `${s.income_uzs.toLocaleString()} so'm` : '0');
        return `
          <div class="source-item">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
              <span style="font-size: 1.6rem;">${s.icon}</span>
              <div>
                <strong style="font-size: 0.95rem; color: var(--dark);">${s.label}</strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);">${s.count} ta muvaffaqiyatli bron</div>
              </div>
            </div>
            <strong style="font-size: 1.05rem; color: var(--primary);">${incomeText}</strong>
          </div>
        `;
      }).join('');

      // Monthly
      const monthlyContainer = document.getElementById('reportMonthlyList');
      monthlyContainer.innerHTML = (rep.monthly_trend || []).map(m => {
        const incomeText = m.income_usd > 0 ? `$${m.income_usd.toLocaleString()}` : (m.income_uzs > 0 ? `${m.income_uzs.toLocaleString()} so'm` : '0');
        return `
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px dashed var(--border);">
            <strong style="font-size: 0.9rem; color: var(--dark);">${m.month_name}</strong>
            <div style="display: flex; gap: 1.25rem; align-items: center;">
              <span style="font-size: 0.8rem; color: var(--text-muted);">${m.bookings_count} bron</span>
              <strong style="font-size: 0.95rem; color: var(--primary);">${incomeText}</strong>
            </div>
          </div>
        `;
      }).join('');

      // Dachas
      const dachasContainer = document.getElementById('reportDachasList');
      if (rep.dachas_breakdown && rep.dachas_breakdown.length > 0) {
        dachasContainer.innerHTML = rep.dachas_breakdown.map(d => {
          const incomeText = d.income_usd > 0 ? `$${d.income_usd.toLocaleString()}` : `${d.income_uzs.toLocaleString()} so'm`;
          return `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1.25rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border);">
              <div>
                <strong style="font-size: 1rem; color: var(--dark);">${escapeHtml(d.name)}</strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);">${d.bookings_count} ta muvaffaqiyatli bron</div>
              </div>
              <strong style="font-size: 1.1rem; color: var(--primary);">${incomeText}</strong>
            </div>
          `;
        }).join('');
      } else {
        dachasContainer.innerHTML = `<p style="color: var(--text-muted); font-size: 0.85rem;">Dachalar mavjud emas.</p>`;
      }
    }

    // ==========================================
    // DACHAS DATA
    // ==========================================
    async function loadDachasData() {
      const container = document.getElementById('ownerDachasListContainer');
      try {
        const res = await fetch(`${API_BASE}/owner/dachas`, {
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });

        if (res.ok) {
          const data = await res.json();
          ownerDachas = data.data || [];
          document.getElementById('ownerDachasCountBadge').textContent = ownerDachas.length;
          renderOwnerDachas(ownerDachas);
          populateManualDachaSelect(ownerDachas);
        }
      } catch (err) {
        console.error('loadDachasData error:', err);
      }
    }

    function renderOwnerDachas(dachas) {
      const container = document.getElementById('ownerDachasListContainer');
      if (!dachas || dachas.length === 0) {
        container.innerHTML = `
          <div style="text-align: center; padding: 4rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🏡</div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hozircha e'lonlaringiz yo'q</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Yangi dacha e'lonini joylashtiring va darhol mijozlar qabul qiling.</p>
            <button class="btn btn-primary" onclick="openCreateDachaModal()">➕ Yangi e'lon joylash</button>
          </div>
        `;
        return;
      }

      container.innerHTML = `
        <div class="owner-dacha-grid">
          ${dachas.map(d => {
            const firstImg = d.media && d.media.length > 0 ? d.media[0].url : '/storage/dachas/images/dacha_1_1.jpg';
            const curr = d.currency === 'UZS' ? 'so\'m' : '$';
            const statusBadge = d.status === 'active' 
              ? '<span class="badge" style="background:#10b981; color:white; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:800;">● Faol</span>'
              : '<span class="badge" style="background:#f59e0b; color:white; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:800;">● Kutilmoqda</span>';

            return `
              <div style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
                <div style="position: relative; height: 180px;">
                  <img src="${firstImg}" style="width: 100%; height: 100%; object-fit: cover;" />
                  <div style="position: absolute; top: 12px; left: 12px;">${statusBadge}</div>
                </div>
                <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                  <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin: 0 0 0.25rem;">${escapeHtml(d.name)}</h3>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.6rem;">📍 ${escapeHtml(d.region || '')}, ${escapeHtml(d.district || '')}</div>
                    <div style="font-size: 0.85rem; margin-bottom: 0.85rem;">👥 ${d.capacity || 1} kishilik • 🛏️ ${d.rooms_count || 1} xona</div>
                    <div style="background: #f8fafc; padding: 0.65rem 0.85rem; border-radius: var(--radius-sm); font-size: 0.85rem; display: flex; justify-content: space-between; margin-bottom: 1rem;">
                      <span>Ish kunlari: <strong>${(d.weekday_price || 0).toLocaleString()} ${curr}</strong></span>
                      <span style="color: var(--accent);">Dam olish: <strong>${(d.weekend_price || 0).toLocaleString()} ${curr}</strong></span>
                    </div>
                  </div>
                  <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-outline" style="flex: 1; padding: 0.45rem; font-size: 0.8rem; text-align: center;" onclick="openDachaDetailModal(${d.id})">👁️ Ko'rish</button>
                    <button class="btn btn-outline" style="flex: 1; padding: 0.45rem; font-size: 0.8rem; color: #2563eb; border-color: #bfdbfe;" onclick="openEditDachaModal(${d.id})">✏️ Tahrirlash</button>
                    <button class="btn btn-outline" style="padding: 0.45rem 0.65rem; color: #d97706; border-color: #fde68a;" onclick="openManualBookingForDacha(${d.id})" title="Tashqi bron kiritish">➕</button>
                    <button class="btn btn-outline" style="padding: 0.45rem 0.65rem; color: #ef4444; border-color: #fecaca;" onclick="deleteDacha(${d.id})" title="O'chirish">🗑️</button>
                  </div>
                </div>
              </div>
            `;
          }).join('')}
        </div>
      `;
    }

    function populateManualDachaSelect(dachas) {
      const select = document.getElementById('manualDachaSelect');
      if (!select) return;
      if (!dachas || dachas.length === 0) {
        select.innerHTML = '<option value="">Avval dacha qo\'shing</option>';
        return;
      }
      select.innerHTML = dachas.map(d => `<option value="${d.id}">${escapeHtml(d.name)} (${d.district || d.region})</option>`).join('');
    }

    // ==========================================
    // BOOKINGS DATA
    // ==========================================
    async function loadBookingsData() {
      const status = document.getElementById('bookingStatusFilter')?.value || '';
      const source = document.getElementById('bookingSourceFilter')?.value || '';
      let url = `${API_BASE}/owner/bookings?`;
      if (status) url += `status=${status}&`;
      if (source) url += `source=${source}&`;

      try {
        const res = await fetch(url, {
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });

        if (res.ok) {
          const data = await res.json();
          ownerBookings = data.data || [];
          document.getElementById('ownerBookingsCountBadge').textContent = ownerBookings.length;
          renderOwnerBookings(ownerBookings);
        }
      } catch (err) {
        console.error('loadBookingsData error:', err);
      }
    }

    function renderOwnerBookings(bookings) {
      const container = document.getElementById('ownerBookingsListContainer');
      if (!bookings || bookings.length === 0) {
        container.innerHTML = `
          <div style="text-align: center; padding: 3.5rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">📋</div>
            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hozircha bronlar mavjud emas</h4>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Mijozlar dachangizni bron qilishganda yoki o'zingiz Telegram orqali kiritganingizda shu yerda ko'rinadi.</p>
          </div>
        `;
        return;
      }

      container.innerHTML = bookings.map(b => {
        const dachaName = b.dacha?.name || 'Dacha';
        const guestName = b.user?.name || b.customer_name || 'Mijoz';
        const guestPhone = b.user?.phone || b.customer_phone || 'Kiritilmagan';
        const startDate = b.start_date ? b.start_date.split('T')[0] : '';
        const endDate = b.end_date ? b.end_date.split('T')[0] : '';
        const totalPrice = parseFloat(b.total_price || 0).toLocaleString();
        const currency = b.currency || 'USD';
        const isPending = b.status === 'pending';
        const isConfirmed = b.status === 'confirmed';
        const isCancelled = b.status === 'cancelled';

        let sourceBadge = `<span style="font-size:0.75rem; background:#e0f2fe; color:#0369a1; padding:3px 10px; border-radius:8px; font-weight:800;">🌟 Oromgo</span>`;
        if (b.source === 'telegram') sourceBadge = `<span style="font-size:0.75rem; background:#dbeafe; color:#1d4ed8; padding:3px 10px; border-radius:8px; font-weight:800;">📱 Telegram</span>`;
        if (b.source === 'phone') sourceBadge = `<span style="font-size:0.75rem; background:#fef3c7; color:#b45309; padding:3px 10px; border-radius:8px; font-weight:800;">📞 Telefon</span>`;
        if (b.source === 'manual') sourceBadge = `<span style="font-size:0.75rem; background:#f1f5f9; color:#475569; padding:3px 10px; border-radius:8px; font-weight:800;">🚫 Yopilgan</span>`;

        let statusBadge = `<span class="notif-type-badge booking_created">Kutilmoqda ⏳</span>`;
        if (isConfirmed) statusBadge = `<span class="notif-type-badge booking_confirmed">Tasdiqlangan ✅</span>`;
        if (isCancelled) statusBadge = `<span class="notif-type-badge booking_cancelled">Bekor qilingan ❌</span>`;

        return `
          <div class="owner-booking-card">
            <div class="owner-booking-header">
              <div>
                <div style="font-weight: 800; font-size: 1.15rem; color: var(--dark); display: flex; align-items: center; gap: 0.6rem;">
                  🏡 ${escapeHtml(dachaName)} ${sourceBadge}
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">Bron raqami: #${b.id}</div>
              </div>
              <div style="display: flex; align-items: center; gap: 0.6rem;">
                ${statusBadge}
                ${(b.source !== 'app' || isCancelled) ? `
                  <button class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; color: #ef4444; border-color: #fecaca;" onclick="deleteBooking(${b.id})" title="O'chirish">🗑️</button>
                ` : ''}
              </div>
            </div>

            <div class="owner-booking-grid">
              <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">👤 Mijoz:</span>
                <strong>${escapeHtml(guestName)}</strong>
              </div>
              <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">📞 Telefon:</span>
                <strong>${escapeHtml(guestPhone)}</strong>
              </div>
              <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">📅 Sanalar:</span>
                <strong>${startDate} — ${endDate}</strong>
              </div>
              <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">💰 Jami summa:</span>
                <strong style="color: var(--primary); font-size: 1rem;">${totalPrice} ${currency}</strong>
              </div>
              ${b.notes ? `
                <div style="grid-column: 1/-1;">
                  <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">💬 Izoh / Sabab:</span>
                  <span style="color: var(--dark);">${escapeHtml(b.notes)}</span>
                </div>
              ` : ''}
            </div>

            ${isPending ? `
              <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                <button class="btn btn-primary" style="background: #10b981; border: none; padding: 0.5rem 1.25rem; font-size: 0.875rem;" onclick="handleBookingAction(${b.id}, 'confirm')">
                  ✅ Tasdiqlash
                </button>
                <button class="btn btn-primary" style="background: #ef4444; border: none; padding: 0.5rem 1.25rem; font-size: 0.875rem;" onclick="handleBookingAction(${b.id}, 'reject')">
                  ❌ Rad etish
                </button>
              </div>
            ` : ''}
          </div>
        `;
      }).join('');
    }

    async function handleBookingAction(bookingId, action) {
      try {
        const res = await fetch(`${API_BASE}/owner/bookings/${bookingId}/${action}`, {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (res.ok) {
          showToast(action === 'confirm' ? '🎉 Bron tasdiqlandi!' : 'Bron rad etildi.', 'success');
          await Promise.all([loadBookingsData(), loadReportsData()]);
        } else {
          showToast(data.message || 'Xatolik', 'error');
        }
      } catch (e) {
        showToast('Server bilan bog\'lanishda xatolik', 'error');
      }
    }

    async function deleteBooking(bookingId) {
      if (!confirm('Ushbu tashqi bron yoki yopilgan sanalarni o\'chirmoqchimisiz?')) return;
      try {
        const res = await fetch(`${API_BASE}/owner/bookings/${bookingId}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          showToast('Bron o\'chirildi.', 'success');
          await Promise.all([loadBookingsData(), loadReportsData()]);
        }
      } catch (e) {
        showToast('Xatolik yuz berdi', 'error');
      }
    }

    // ==========================================
    // MANUAL BOOKING FORM
    // ==========================================
    function updateManualFormPlaceholders() {
      const src = document.querySelector('input[name="manualBookingSource"]:checked')?.value || 'telegram';
      const priceInput = document.getElementById('manualPrice');
      const notesInput = document.getElementById('manualNotes');
      if (src === 'manual') {
        priceInput.placeholder = "0 (Yopish uchun narx shart emas)";
        notesInput.placeholder = "Ta'mirlash yoki o'zimiz dam olamiz";
      } else if (src === 'telegram') {
        priceInput.placeholder = "Masalan: 1500000";
        notesInput.placeholder = "Telegram orqali kelishildi";
      } else {
        priceInput.placeholder = "Masalan: 1500000";
        notesInput.placeholder = "Telefon orqali bron qilindi";
      }
    }

    async function handleManualBookingSubmit(e) {
      e.preventDefault();
      const dachaId = document.getElementById('manualDachaSelect')?.value;
      const startDate = document.getElementById('manualStartDate')?.value;
      const endDate = document.getElementById('manualEndDate')?.value;
      const source = document.querySelector('input[name="manualBookingSource"]:checked')?.value || 'telegram';
      const price = parseFloat(document.getElementById('manualPrice')?.value || 0);
      const currency = document.getElementById('manualCurrency')?.value || 'USD';
      const customerName = document.getElementById('manualCustomerName')?.value;
      const customerPhone = document.getElementById('manualCustomerPhone')?.value;
      const notes = document.getElementById('manualNotes')?.value;

      if (!dachaId || !startDate || !endDate) {
        showToast('Majburiy maydonlarni to\'ldiring', 'error');
        return;
      }

      try {
        let endpoint = `${API_BASE}/owner/bookings/manual`;
        let bodyData = {
          dacha_id: parseInt(dachaId),
          start_date: startDate,
          end_date: endDate,
          total_price: price,
          currency: currency,
          source: source,
          customer_name: customerName || null,
          customer_phone: customerPhone || null,
          notes: notes || null
        };

        if (source === 'manual' && price === 0) {
          endpoint = `${API_BASE}/owner/dachas/${dachaId}/block-dates`;
          bodyData = {
            start_date: startDate,
            end_date: endDate,
            reason: notes,
            total_price: 0,
            currency: currency,
            source: 'manual',
            customer_name: customerName,
            customer_phone: customerPhone
          };
        }

        const res = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
          body: JSON.stringify(bodyData)
        });

        const data = await res.json();
        if (res.ok) {
          showToast('🎉 Tashqi bron saqlandi va hisobotga kiritildi!', 'success');
          document.getElementById('ownerManualBookingForm').reset();
          await Promise.all([loadBookingsData(), loadReportsData()]);
          switchOwnerTab('reports', document.querySelector('.owner-nav-tab'));
        } else {
          showToast(data.message || 'Xatolik yuz berdi', 'error');
        }
      } catch (err) {
        showToast('Server bilan bog\'lanishda xatolik', 'error');
      }
    }

    // ==========================================
    // LOCATIONS & CRUD
    // ==========================================
    async function loadLocations() {
      try {
        const res = await fetch(`${API_BASE}/locations`);
        if (res.ok) {
          locationsData = await res.json();
          populateRegionSelects();
        }
      } catch (e) {}
    }

    function populateRegionSelects() {
      const regSelect = document.getElementById('formDachaRegion');
      if (!regSelect) return;
      const regions = Object.keys(locationsData || {});
      regSelect.innerHTML = '<option value="">Viloyatni tanlang</option>' + regions.map(r => `<option value="${r}">${r}</option>`).join('');
    }

    function handleRegionChange() {
      const reg = document.getElementById('formDachaRegion')?.value;
      const distSelect = document.getElementById('formDachaDistrict');
      if (!distSelect) return;
      if (reg && locationsData[reg]) {
        const districts = Array.isArray(locationsData[reg]) 
          ? locationsData[reg] 
          : Object.keys(locationsData[reg]);
        distSelect.innerHTML = '<option value="">Tumanni tanlang</option>' + districts.map(d => `<option value="${d}">${d}</option>`).join('');
      } else {
        distSelect.innerHTML = '<option value="">Avval viloyatni tanlang</option>';
      }
    }

    function openCreateDachaModal() {
      document.getElementById('dachaModalTitle').textContent = "🏡 Yangi dacha e'lonini joylash";
      document.getElementById('editDachaId').value = "";
      document.getElementById('ownerDachaForm').reset();
      document.getElementById('formExistingMediaContainer').innerHTML = '';
      populateRegionSelects();
      handleRegionChange();
      openModal('dachaFormModal');
    }

    function openEditDachaModal(id) {
      const d = ownerDachas.find(item => item.id == id);
      if (!d) {
        console.error('Dacha topilmadi:', id);
        return;
      }
      document.getElementById('dachaModalTitle').textContent = "✏️ Dachani tahrirlash";
      document.getElementById('editDachaId').value = d.id;
      document.getElementById('formDachaName').value = d.name || '';
      document.getElementById('formDachaDescription').value = d.description || '';
      document.getElementById('formDachaCapacity').value = d.capacity || 10;
      document.getElementById('formDachaRooms').value = d.rooms_count || 4;
      document.getElementById('formDachaCurrency').value = d.currency || 'USD';
      document.getElementById('formDachaWeekdayPrice').value = d.weekday_price || '';
      document.getElementById('formDachaWeekendPrice').value = d.weekend_price || '';

      // Rasmlarni tozalash
      document.getElementById('formDachaImages').value = '';
      document.getElementById('formDachaVideo').value = '';

      // Eski medialarni ko'rsatish
      const mediaContainer = document.getElementById('formExistingMediaContainer');
      mediaContainer.innerHTML = '';
      if (d.media && d.media.length > 0) {
        d.media.forEach(m => {
          const div = document.createElement('div');
          div.style = "position: relative; width: 80px; height: 80px; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border);";
          if (m.type === 'video') {
            div.innerHTML = `<video src="${m.url}" style="width: 100%; height: 100%; object-fit: cover;" muted></video>
                             <button type="button" style="position: absolute; top: 2px; right: 2px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 10px; cursor: pointer;" onclick="deleteDachaMedia(${m.id}, ${d.id})">✕</button>
                             <span style="position: absolute; bottom: 2px; left: 2px; background: rgba(0,0,0,0.6); color: white; font-size: 8px; padding: 2px 4px; border-radius: 4px;">🎬 Vid</span>`;
          } else {
            div.innerHTML = `<img src="${m.url}" style="width: 100%; height: 100%; object-fit: cover;" />
                             <button type="button" style="position: absolute; top: 2px; right: 2px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 10px; cursor: pointer;" onclick="deleteDachaMedia(${m.id}, ${d.id})">✕</button>`;
          }
          mediaContainer.appendChild(div);
        });
      }

      populateRegionSelects();
      
      // Select region
      const regSelect = document.getElementById('formDachaRegion');
      if (d.region) {
        // Agar viloyat ro'yxatda bo'lmasa, qo'shib qo'yamiz
        let exists = Array.from(regSelect.options).some(opt => opt.value === d.region);
        if (!exists) {
          const opt = document.createElement('option');
          opt.value = d.region;
          opt.textContent = d.region;
          regSelect.appendChild(opt);
        }
        regSelect.value = d.region;
      }
      handleRegionChange();

      // Select district
      const distSelect = document.getElementById('formDachaDistrict');
      if (d.district) {
        let exists = Array.from(distSelect.options).some(opt => opt.value === d.district);
        if (!exists) {
          const opt = document.createElement('option');
          opt.value = d.district;
          opt.textContent = d.district;
          distSelect.appendChild(opt);
        }
        distSelect.value = d.district;
      }

      openModal('dachaFormModal');
    }

    async function handleSaveDacha(e) {
      e.preventDefault();
      const editId = document.getElementById('editDachaId').value;
      
      const formData = new FormData();
      formData.append('name', document.getElementById('formDachaName').value);
      formData.append('description', document.getElementById('formDachaDescription').value);
      formData.append('region', document.getElementById('formDachaRegion').value);
      formData.append('district', document.getElementById('formDachaDistrict').value);
      formData.append('capacity', document.getElementById('formDachaCapacity').value);
      formData.append('rooms_count', document.getElementById('formDachaRooms').value);
      formData.append('currency', document.getElementById('formDachaCurrency').value);
      formData.append('weekday_price', document.getElementById('formDachaWeekdayPrice').value);
      formData.append('weekend_price', document.getElementById('formDachaWeekendPrice').value);

      const imagesInput = document.getElementById('formDachaImages');
      if (imagesInput.files.length > 7) {
        showToast('Eng ko\'pi bilan 7 ta rasm yuklash mumkin', 'error');
        return;
      }
      for (let i = 0; i < imagesInput.files.length; i++) {
        formData.append('images[]', imagesInput.files[i]);
      }

      const videoInput = document.getElementById('formDachaVideo');
      if (videoInput.files.length > 0) {
        formData.append('videos[]', videoInput.files[0]);
      }

      if (editId) {
        formData.append('_method', 'PUT');
      }

      try {
        const url = editId ? `${API_BASE}/owner/dachas/${editId}` : `${API_BASE}/owner/dachas`;

        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
          body: formData
        });

        const data = await res.json();
        if (res.ok) {
          showToast(editId ? 'Dacha yangilandi!' : 'Dacha muvaffaqiyatli qo\'shildi!', 'success');
          closeModal('dachaFormModal');
          await Promise.all([loadDachasData(), loadReportsData()]);
        } else {
          showToast(data.message || 'Xatolik', 'error');
        }
      } catch (err) {
        showToast('Server xatosi', 'error');
      }
    }

    async function deleteDachaMedia(mediaId, dachaId) {
      if (!confirm('Rostdan ham ushbu faylni o\'chirmoqchimisiz?')) return;
      try {
        const res = await fetch(`${API_BASE}/owner/media/${mediaId}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          showToast('Media fayl o\'chirildi', 'success');
          await loadDachasData();
          openEditDachaModal(dachaId);
        } else {
          const data = await res.json();
          showToast(data.message || 'Xatolik', 'error');
        }
      } catch (e) {
        showToast('Xatolik yuz berdi', 'error');
      }
    }

    function openDachaDetailModal(id) {
      const d = ownerDachas.find(item => item.id == id);
      if (!d) return;

      const firstImg = d.media && d.media.length > 0 ? d.media[0].url : '/storage/dachas/images/dacha_1_1.jpg';
      const curr = d.currency === 'UZS' ? 'so\'m' : '$';

      document.getElementById('detailCoverImg').src = firstImg;
      document.getElementById('detailDachaTitle').textContent = d.name || 'Dacha';
      document.getElementById('detailDachaLocation').textContent = `📍 ${d.region || ''}, ${d.district || ''}`;
      
      const statusBadge = d.status === 'active' 
        ? '<span class="badge" style="background:#10b981; color:white; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:800;">● Faol e\'lon</span>'
        : '<span class="badge" style="background:#f59e0b; color:white; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:800;">● Kutilmoqda</span>';
      document.getElementById('detailStatusBadge').innerHTML = statusBadge;

      document.getElementById('detailWeekdayPrice').textContent = `${(d.weekday_price || 0).toLocaleString()} ${curr}`;
      document.getElementById('detailWeekendPrice').textContent = `${(d.weekend_price || 0).toLocaleString()} ${curr}`;
      document.getElementById('detailCapacityRooms').textContent = `👥 ${d.capacity || 1} kishilik • 🛏️ ${d.rooms_count || 1} xona`;
      document.getElementById('detailDescription').textContent = d.description || 'Tavsif kiritilmagan.';

      // Edit and Manual buttons
      document.getElementById('detailEditBtn').onclick = () => {
        closeModal('dachaDetailModal');
        openEditDachaModal(d.id);
      };

      document.getElementById('detailAddManualBookingBtn').onclick = () => {
        closeModal('dachaDetailModal');
        openManualBookingForDacha(d.id);
      };

      // Bookings for this dacha
      const dachaBookings = ownerBookings.filter(b => b.dacha_id === d.id);
      const bookingsContainer = document.getElementById('detailDachaBookingsList');
      if (dachaBookings.length === 0) {
        bookingsContainer.innerHTML = `<div style="padding: 1.25rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px dashed var(--border); text-align: center; color: var(--text-muted); font-size: 0.85rem;">Hozircha ushbu dacha uchun bronlar yo'q.</div>`;
      } else {
        bookingsContainer.innerHTML = `
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            ${dachaBookings.map(b => {
              const guestName = b.user?.name || b.customer_name || 'Mijoz';
              const startDate = b.start_date ? b.start_date.split('T')[0] : '';
              const endDate = b.end_date ? b.end_date.split('T')[0] : '';
              const totalPrice = parseFloat(b.total_price || 0).toLocaleString();
              const bCurr = b.currency || 'USD';
              let srcBadge = `<span style="font-size:0.75rem; background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:6px; font-weight:700;">Oromgo 🌟</span>`;
              if (b.source === 'telegram') srcBadge = `<span style="font-size:0.75rem; background:#dbeafe; color:#1d4ed8; padding:2px 6px; border-radius:6px; font-weight:700;">Telegram 📱</span>`;
              if (b.source === 'phone') srcBadge = `<span style="font-size:0.75rem; background:#fef3c7; color:#b45309; padding:2px 6px; border-radius:6px; font-weight:700;">Telefon 📞</span>`;
              if (b.source === 'manual') srcBadge = `<span style="font-size:0.75rem; background:#f1f5f9; color:#475569; padding:2px 6px; border-radius:6px; font-weight:700;">Yopilgan 🚫</span>`;

              return `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.85rem;">
                  <div>
                    <strong>👤 ${escapeHtml(guestName)}</strong> ${srcBadge}
                    <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.15rem;">📅 ${startDate} — ${endDate}</div>
                  </div>
                  <strong style="color: var(--primary);">${totalPrice} ${bCurr}</strong>
                </div>
              `;
            }).join('')}
          </div>
        `;
      }

      openModal('dachaDetailModal');
    }

    function openManualBookingForDacha(dachaId) {
      switchOwnerTab('manualBooking', document.querySelectorAll('.owner-nav-tab')[3]);
      setTimeout(() => {
        const select = document.getElementById('manualDachaSelect');
        if (select && dachaId) {
          select.value = dachaId;
        }
      }, 100);
    }

    async function deleteDacha(id) {
      if (!confirm('Ushbu dacha e\'lonini o\'chirmoqchimisiz?')) return;
      try {
        const res = await fetch(`${API_BASE}/owner/dachas/${id}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          showToast('Dacha o\'chirildi', 'success');
          await Promise.all([loadDachasData(), loadReportsData()]);
        }
      } catch (e) {
        showToast('Xatolik', 'error');
      }
    }

    function logoutOwner() {
      localStorage.removeItem('oromgo_token');
      localStorage.removeItem('oromgo_user');
      window.location.href = '/';
    }

    function openModal(id) {
      document.getElementById(id)?.classList.add('open');
    }
    function closeModal(id) {
      document.getElementById(id)?.classList.remove('open');
    }

    function showToast(msg, type = 'info') {
      const c = document.getElementById('toastContainer');
      const t = document.createElement('div');
      t.className = `toast toast-${type}`;
      t.textContent = msg;
      c.appendChild(t);
      setTimeout(() => t.classList.add('show'), 10);
      setTimeout(() => {
        t.classList.remove('show');
        setTimeout(() => t.remove(), 300);
      }, 3500);
    }

    function escapeHtml(text) {
      if (!text) return '';
      return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
  </script>
</body>
</html>
