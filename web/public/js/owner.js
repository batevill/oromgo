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

      initMediaDropzones();
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
            const firstImg = d.media && d.media.length > 0 ? (d.media[0].thumbnail_url || d.media[0].url) : '/storage/dachas/images/dacha_1_1.jpg';
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

    // ==========================================
    // MULTI-IMAGE & MEDIA STAGING SYSTEM
    // ==========================================
    let stagedImages = [];
    let stagedVideo = null;

    function initMediaDropzones() {
      const imgDropzone = document.getElementById('imagesDropzone');
      const vidDropzone = document.getElementById('videoDropzone');

      if (imgDropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
          imgDropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            imgDropzone.classList.add('dragover');
          }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
          imgDropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            imgDropzone.classList.remove('dragover');
          }, false);
        });

        imgDropzone.addEventListener('drop', (e) => {
          const dt = e.dataTransfer;
          if (dt && dt.files && dt.files.length > 0) {
            handleImageInputFiles(dt.files);
          }
        }, false);
      }

      if (vidDropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
          vidDropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            vidDropzone.classList.add('dragover');
          }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
          vidDropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            vidDropzone.classList.remove('dragover');
          }, false);
        });

        vidDropzone.addEventListener('drop', (e) => {
          const dt = e.dataTransfer;
          if (dt && dt.files && dt.files.length > 0) {
            handleVideoInputFile(dt.files[0]);
          }
        }, false);
      }
    }

    function formatFileSize(bytes) {
      if (!bytes || bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    async function compressImageToWebP(file) {
      return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = new Image();
          img.onload = function() {
            let width = img.width;
            let height = img.height;
            const MAX_WIDTH = 1920;
            const MAX_HEIGHT = 1080;
            
            if (width > height) {
              if (width > MAX_WIDTH) {
                height *= MAX_WIDTH / width;
                width = MAX_WIDTH;
              }
            } else {
              if (height > MAX_HEIGHT) {
                width *= MAX_HEIGHT / height;
                height = MAX_HEIGHT;
              }
            }
            
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            canvas.toBlob((blob) => {
              if (!blob) {
                 resolve(file); 
                 return;
              }
              const newName = file.name.replace(/\.[^/.]+$/, "") + ".webp";
              const newFile = new File([blob], newName, {
                type: 'image/webp',
                lastModified: Date.now()
              });
              resolve(newFile);
            }, 'image/webp', 0.85); 
          };
          img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      });
    }

    async function handleImageInputFiles(files) {
      if (!files || files.length === 0) return;

      const maxLimit = 10;
      let addedCount = 0;
      
      showToast('Rasmlar siqilmoqda (WebP), kuting...', 'info');

      for (let i = 0; i < files.length; i++) {
        let file = files[i];

        if (!file.type.startsWith('image/')) {
          showToast(`"${file.name}" rasm fayli emas!`, 'error');
          continue;
        }

        if (stagedImages.length >= maxLimit) {
          showToast(`Eng ko'pi bilan ${maxLimit} ta rasm yuklash mumkin!`, 'warning');
          break;
        }

        const baseName = file.name.replace(/\.[^/.]+$/, "");
        const exists = stagedImages.some(f => f.name.replace(/\.[^/.]+$/, "") === baseName);
        if (!exists) {
          file = await compressImageToWebP(file);
          stagedImages.push(file);
          addedCount++;
        }
      }

      const fileInput = document.getElementById('formDachaImages');
      if (fileInput) fileInput.value = '';

      renderStagedImagePreviews();
      if (addedCount > 0) {
        showToast(`${addedCount} ta rasm siqildi va tayyor`, 'success');
      }
    }

    function renderStagedImagePreviews() {
      const grid = document.getElementById('formStagedImagesGrid');
      const counter = document.getElementById('stagedImagesCount');
      if (!grid || !counter) return;

      counter.textContent = `${stagedImages.length} ta tanlandi (max 10)`;

      if (stagedImages.length === 0) {
        grid.style.display = 'none';
        grid.innerHTML = '';
        return;
      }

      grid.style.display = 'grid';
      grid.innerHTML = '';

      stagedImages.forEach((file, index) => {
        const card = document.createElement('div');
        card.className = `preview-card ${index === 0 ? 'is-cover' : ''}`;

        const objectUrl = URL.createObjectURL(file);

        card.innerHTML = `
          <img src="${objectUrl}" alt="${file.name}" onload="URL.revokeObjectURL(this.src)" />
          ${index === 0 ? '<span class="badge-tag badge-cover">🌟 Asosiy Muqova</span>' : ''}
          <button type="button" class="btn-remove-preview" title="O'chirish" onclick="removeStagedImage(${index})">✕</button>
          <div class="preview-meta-info">${file.name} (${formatFileSize(file.size)})</div>
        `;

        grid.appendChild(card);
      });
    }

    function removeStagedImage(index) {
      if (index >= 0 && index < stagedImages.length) {
        stagedImages.splice(index, 1);
        renderStagedImagePreviews();
      }
    }

    function handleVideoInputFile(file) {
      if (!file) return;

      if (!file.type.startsWith('video/')) {
        showToast(`"${file.name}" video fayli emas!`, 'error');
        return;
      }

      if (file.size > 50 * 1024 * 1024) {
        showToast(`Video 50 MB dan katta bo'lmasligi kerak (Hajmi: ${formatFileSize(file.size)})`, 'error');
        return;
      }

      stagedVideo = file;
      renderStagedVideoPreview();
      showToast('Video yuklashga tanlandi', 'success');
    }

    function renderStagedVideoPreview() {
      const container = document.getElementById('formStagedVideoContainer');
      const counter = document.getElementById('stagedVideoCount');
      if (!container || !counter) return;

      if (!stagedVideo) {
        container.style.display = 'none';
        container.innerHTML = '';
        counter.textContent = '0 video';
        return;
      }

      counter.textContent = '1 video tanlandi';
      container.style.display = 'block';

      const videoUrl = URL.createObjectURL(stagedVideo);

      container.innerHTML = `
        <div class="preview-card" style="aspect-ratio: 16/9; max-width: 320px;">
          <video src="${videoUrl}" controls style="width: 100%; height: 100%; object-fit: cover;"></video>
          <span class="badge-tag" style="background: #8b5cf6; color: white;">🎬 Yangi Video</span>
          <button type="button" class="btn-remove-preview" title="O'chirish" onclick="removeStagedVideo()">✕</button>
          <div class="preview-meta-info">${stagedVideo.name} (${formatFileSize(stagedVideo.size)})</div>
        </div>
      `;
    }

    function removeStagedVideo() {
      stagedVideo = null;
      const fileInput = document.getElementById('formDachaVideo');
      if (fileInput) fileInput.value = '';
      renderStagedVideoPreview();
    }

    function openCreateDachaModal() {
      document.getElementById('dachaModalTitle').textContent = "🏡 Yangi dacha e'lonini joylash";
      document.getElementById('dachaModalSubtitle').textContent = "Dachangiz haqidagi ma'lumotlarni to'ldiring va bir vaqtning o'zida bir nechta rasm yuklang";
      document.getElementById('editDachaId').value = "";
      document.getElementById('ownerDachaForm').reset();
      
      // Clear media staging
      stagedImages = [];
      stagedVideo = null;
      renderStagedImagePreviews();
      renderStagedVideoPreview();

      // Hide existing media section
      const existingSec = document.getElementById('formExistingMediaSection');
      if (existingSec) existingSec.style.display = 'none';
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
      document.getElementById('dachaModalSubtitle').textContent = "Dacha parametrlarini o'zgartiring, yangi rasmlar qo'shing yoki mavjudlarini o'chiring";
      document.getElementById('editDachaId').value = d.id;
      document.getElementById('formDachaName').value = d.name || '';
      document.getElementById('formDachaDescription').value = d.description || '';
      document.getElementById('formDachaCapacity').value = d.capacity || 10;
      document.getElementById('formDachaRooms').value = d.rooms_count || 4;
      document.getElementById('formDachaCurrency').value = d.currency || 'USD';
      document.getElementById('formDachaWeekdayPrice').value = d.weekday_price || '';
      document.getElementById('formDachaWeekendPrice').value = d.weekend_price || '';

      // Reset staging
      stagedImages = [];
      stagedVideo = null;
      renderStagedImagePreviews();
      renderStagedVideoPreview();

      // Eski Google Drive medialarini ko'rsatish
      const existingSection = document.getElementById('formExistingMediaSection');
      const mediaContainer = document.getElementById('formExistingMediaContainer');
      const existingCount = document.getElementById('existingMediaCount');
      mediaContainer.innerHTML = '';

      if (d.media && d.media.length > 0) {
        existingSection.style.display = 'block';
        existingCount.textContent = `${d.media.length} ta fayl mavjud`;

        d.media.forEach((m, idx) => {
          const card = document.createElement('div');
          card.className = `preview-card ${idx === 0 && m.type !== 'video' ? 'is-cover' : ''}`;
          
          if (m.type === 'video') {
            card.innerHTML = `
              <video src="${m.url}" style="width: 100%; height: 100%; object-fit: cover;" muted></video>
              <span class="badge-tag" style="background: #8b5cf6; color: white;">🎬 Video</span>
              <button type="button" class="btn-remove-preview" title="Google Drive-dan o'chirish" onclick="deleteDachaMedia(${m.id}, ${d.id})">✕</button>
              <span class="badge-type">Google Drive</span>
            `;
          } else {
            card.innerHTML = `
              <img src="${m.url}" alt="Dacha rasm" loading="lazy" />
              ${idx === 0 ? '<span class="badge-tag badge-cover">🌟 Asosiy Muqova</span>' : '<span class="badge-tag badge-drive">☁️ Drive</span>'}
              <button type="button" class="btn-remove-preview" title="Google Drive-dan o'chirish" onclick="deleteDachaMedia(${m.id}, ${d.id})">✕</button>
            `;
          }
          mediaContainer.appendChild(card);
        });
      } else {
        existingSection.style.display = 'none';
      }

      populateRegionSelects();
      
      // Select region
      const regSelect = document.getElementById('formDachaRegion');
      if (d.region) {
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
      const submitBtn = document.getElementById('btnSaveDachaSubmit');
      
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

      for (let i = 0; i < stagedImages.length; i++) {
        formData.append('images[]', stagedImages[i]);
      }
      if (stagedVideo) {
        formData.append('videos[]', stagedVideo);
      }
      if (editId) {
        formData.append('_method', 'PUT');
      }

      const originalBtnHtml = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span class="spinner-loading"></span> <span>Yuklanmoqda... Kuting</span>`;

      const progressContainer = document.getElementById('uploadProgressContainer');
      const progressBar = document.getElementById('uploadProgressBar');
      const progressText = document.getElementById('uploadProgressText');
      if (progressContainer) {
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = '0%';
      }

      const url = editId ? `${API_BASE}/owner/dachas/${editId}` : `${API_BASE}/owner/dachas`;

      const xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      xhr.setRequestHeader('Authorization', `Bearer ${token}`);
      xhr.setRequestHeader('Accept', 'application/json');

      xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
          const percentComplete = Math.round((e.loaded / e.total) * 100);
          const loadedMB = (e.loaded / (1024 * 1024)).toFixed(1);
          const totalMB = (e.total / (1024 * 1024)).toFixed(1);
          if (progressBar) progressBar.style.width = percentComplete + '%';
          if (progressText) progressText.textContent = `${percentComplete}% (${loadedMB}MB / ${totalMB}MB)`;
        }
      });

      xhr.onload = async function() {
        if (progressContainer) progressContainer.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;

        if (xhr.status >= 200 && xhr.status < 300) {
          showToast(editId ? 'Dacha va rasmlar yangilandi!' : 'Dacha va barcha rasmlar Google Drive-ga muvaffaqiyatli saqlandi! 🎉', 'success');
          closeModal('dachaFormModal');
          stagedImages = [];
          stagedVideo = null;
          await Promise.all([loadDachasData(), loadReportsData()]);
        } else {
          try {
             const data = JSON.parse(xhr.responseText);
             showToast(data.message || 'Saqlashda xatolik yuz berdi', 'error');
          } catch(e) {
             showToast('Saqlashda xatolik yuz berdi', 'error');
          }
        }
      };

      xhr.onerror = function() {
        if (progressContainer) progressContainer.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
        showToast('Server xatosi yuz berdi', 'error');
      };

      xhr.send(formData);
    }

    async function deleteDachaMedia(mediaId, dachaId) {
      if (!confirm('Rostdan ham ushbu faylni Google Drive-dan butunlay o\'chirmoqchimisiz?')) return;
      try {
        const res = await fetch(`${API_BASE}/owner/media/${mediaId}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        if (res.ok) {
          showToast('Fayl Google Drive-dan o\'chirildi', 'success');
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
