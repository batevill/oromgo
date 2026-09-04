<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oromgo - Dacha Egasi Boshqaruv & Hisobot Paneli</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏡</text></svg>">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="/css/owner.css?v={{ time() }}">
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

        <!-- Existing Media Section (for Edit mode) -->
        <div id="formExistingMediaSection" class="media-section-box" style="display: none;">
          <div class="media-section-title">
            <span>☁️ Google Drive-dagi mavjud rasmlar & videolar</span>
            <span class="media-badge-counter" id="existingMediaCount">0 ta fayl</span>
          </div>
          <div id="formExistingMediaContainer" class="preview-gallery-grid" style="margin-top: 0.5rem;"></div>
        </div>

        <!-- New Images Multi-Upload Section -->
        <div class="media-section-box">
          <div class="media-section-title">
            <span>📸 Rasmlar yuklash (Ko'p tanlash / Drag & Drop)</span>
            <span class="media-badge-counter" id="stagedImagesCount">0 ta tanlandi (max 10)</span>
          </div>
          
          <input type="file" id="formDachaImages" accept="image/jpeg,image/png,image/jpg,image/webp" multiple style="display: none;" onchange="handleImageInputFiles(this.files)" />
          
          <div class="dropzone-box" id="imagesDropzone" onclick="document.getElementById('formDachaImages').click()">
            <span class="dropzone-icon">🖼️</span>
            <div class="dropzone-label">Rasmlarni bu yerga tashlang yoki tanlash uchun bosing</div>
            <div class="dropzone-sub">Bir vaqtda bir nechta rasm tanlashingiz mumkin (JPG, PNG, WEBP, har biri max 10MB)</div>
          </div>

          <!-- Staged images preview gallery -->
          <div id="formStagedImagesGrid" class="preview-gallery-grid" style="display: none;"></div>
        </div>

        <!-- Video Upload Section -->
        <div class="media-section-box">
          <div class="media-section-title">
            <span>🎬 Dacha videosi (Ixtiyoriy)</span>
            <span class="media-badge-counter" id="stagedVideoCount">0 video</span>
          </div>

          <input type="file" id="formDachaVideo" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska" style="display: none;" onchange="handleVideoInputFile(this.files[0])" />

          <div class="dropzone-box" id="videoDropzone" onclick="document.getElementById('formDachaVideo').click()">
            <span class="dropzone-icon">📹</span>
            <div class="dropzone-label">Videoni bu yerga tashlang yoki tanlang (Max: 1 ta)</div>
            <div class="dropzone-sub">MP4, MOV format (Max 50MB)</div>
          </div>

          <!-- Staged video preview -->
          <div id="formStagedVideoContainer" style="margin-top: 0.75rem; display: none;"></div>
        </div>

        <button type="submit" id="btnSaveDachaSubmit" class="btn btn-primary" style="width: 100%; padding: 0.95rem; font-size: 1rem; font-weight: 800; margin-top: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
          <span>💾 Saqlash</span>
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
  <script src="/js/owner.js?v={{ time() }}"></script>
</body>
</html>
