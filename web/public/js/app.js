/**
 * OROMGO - FRONTEND APP CLIENT LOGIC (VANILLA JS)
 */

const API_BASE = '/api';

const state = {
  dachas: [],
  amenities: [],
  activeFilter: 'all',
  currentDacha: null,
  bookedDates: [],
  token: localStorage.getItem('oromgo_token') || '',
  user: JSON.parse(localStorage.getItem('oromgo_user') || 'null'),
};

// ==========================================
// INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  initApp();
  setupEventListeners();
  updateAuthUI();
});

async function initApp() {
  await Promise.all([loadAmenities(), loadDachas()]);
}

function setupEventListeners() {
  // Search Form
  const searchForm = document.getElementById('searchForm');
  if (searchForm) {
    searchForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const region = document.getElementById('searchRegion').value;
      const capacity = document.getElementById('searchCapacity').value;
      const currency = document.getElementById('searchCurrency').value;
      const maxPrice = document.getElementById('searchPrice').value;

      const params = {};
      if (region) params.region = region;
      if (capacity) params.capacity = capacity;
      if (currency) params.currency = currency;
      if (maxPrice) params.max_price = maxPrice;

      loadDachas(params);
    });
  }

  // Booking Form Date Inputs
  const startDateInput = document.getElementById('bookStartDate');
  const endDateInput = document.getElementById('bookEndDate');
  if (startDateInput && endDateInput) {
    startDateInput.addEventListener('change', handleDateChange);
    endDateInput.addEventListener('change', handleDateChange);
  }

  // Booking Form Submit
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.addEventListener('submit', handleBookingSubmit);
  }

  // Owner Dacha Create Form Submit
  const createDachaForm = document.getElementById('createDachaForm');
  if (createDachaForm) {
    createDachaForm.addEventListener('submit', handleCreateDacha);
  }
}

// ==========================================
// API CALLS & RENDERING
// ==========================================

async function loadAmenities() {
  try {
    const res = await fetch(`${API_BASE}/amenities`);
    if (!res.ok) throw new Error('Qulayliklarni yuklab bo\'lmadi');
    const data = await res.json();
    state.amenities = data;
    renderAmenitiesForOwnerForm(data);
  } catch (err) {
    console.error(err);
  }
}

async function loadDachas(params = {}) {
  const grid = document.getElementById('dachaGrid');
  if (!grid) return;

  grid.innerHTML = `
    <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
      <div style="display:inline-block; width: 40px; height: 40px; border: 4px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top: 1rem; color: var(--text-muted); font-weight: 600;">Dachalar qidirilmoqda...</p>
    </div>
  `;

  try {
    const queryString = new URLSearchParams(params).toString();
    const res = await fetch(`${API_BASE}/dachas?${queryString}`);
    const result = await res.json();
    state.dachas = result.data || [];

    renderDachas(state.dachas);
  } catch (err) {
    grid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: #ef4444; font-weight: 600;">Ma'lumotlarni yuklashda xatolik yuz berdi.</p>`;
  }
}

function renderDachas(dachas) {
  const grid = document.getElementById('dachaGrid');
  const countEl = document.getElementById('resultsCount');
  if (countEl) countEl.textContent = `${dachas.length} ta dacha topildi`;

  if (!dachas.length) {
    grid.innerHTML = `
      <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">🏡</div>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Mos dacha topilmadi</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Qidiruv parametrlarini o'zgartirib ko'ring yoki barcha dachalarni ko'ring.</p>
        <button class="btn btn-outline" style="margin-top: 1.25rem;" onclick="resetSearch()">Filtrlarni tozalash</button>
      </div>
    `;
    return;
  }

  grid.innerHTML = dachas.map(dacha => {
    const firstImg = dacha.media && dacha.media.length > 0 
      ? dacha.media[0].url 
      : 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&auto=format&fit=crop&q=80';

    const currencySymbol = dacha.currency === 'UZS' ? 'so\'m' : '$';
    const weekdayPrice = parseFloat(dacha.weekday_price || dacha.default_price || 0);
    const weekendPrice = parseFloat(dacha.weekend_price || dacha.weekday_price || dacha.default_price || 0);

    return `
      <div class="dacha-card" onclick="openDachaDetail(${dacha.id})">
        <div class="card-img-wrapper">
          <img src="${firstImg}" alt="${dacha.name}" loading="lazy" />
          <div class="card-badge-location">
            📍 ${dacha.region || ''}, ${dacha.district || ''}
          </div>
          <div class="card-badge-tag">
            ${dacha.currency || 'USD'}
          </div>
        </div>
        <div class="card-content">
          <h3 class="card-title">${dacha.name}</h3>
          <div class="card-specs">
            <span>👥 ${dacha.capacity || 1} kishi</span>
            <span>•</span>
            <span>🛏️ ${dacha.rooms_count || 1} xona</span>
          </div>
          <div class="card-prices">
            <div class="price-item">
              <span class="price-label">Ish kunlari:</span>
              <div class="price-val">${weekdayPrice.toLocaleString()} <span>${currencySymbol}/kun</span></div>
            </div>
            <div class="price-item" style="text-align: right;">
              <span class="price-label">Dam olish:</span>
              <div class="price-val" style="color: var(--accent);">${weekendPrice.toLocaleString()} <span>${currencySymbol}/kun</span></div>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// ==========================================
// DACHA DETAIL & BOOKING MODAL
// ==========================================

async function openDachaDetail(id) {
  // Agar foydalanuvchi tizimga kirmagan bo'lsa, avval Auth modali orqali taklif beramiz
  // lekin mehmonlarga qulaylik uchun to'g'ridan-to'g'ri batafsil ko'rishni ta'minlaymiz
  const modal = document.getElementById('detailModal');
  const content = document.getElementById('detailModalContent');
  if (!modal || !content) return;

  openModal('detailModal');
  content.innerHTML = `
    <div style="padding: 4rem; text-align: center;">
      <div style="display:inline-block; width: 40px; height: 40px; border: 4px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top: 1rem; color: var(--text-muted); font-weight: 600;">Dacha ma'lumotlari yuklanmoqda...</p>
    </div>
  `;

  try {
    const headers = {};
    if (state.token) {
      headers['Authorization'] = `Bearer ${state.token}`;
    }

    const res = await fetch(`${API_BASE}/dachas/${id}`, { credentials: 'omit', headers });
    
    if (res.status === 401) {
      closeModal('detailModal');
      openAuthModal('Dacha haqida batafsil ma\'lumot va uning to\'liq kontaktlarini ko\'rish uchun iltimos, tizimga kiring.');
      return;
    }

    const dacha = await res.json();
    state.currentDacha = dacha;

    // Kalendar bandliklarini yuklash
    loadDachaCalendar(id);

    renderDachaDetail(dacha);
  } catch (err) {
    content.innerHTML = `<p style="padding: 2rem; color: red;">Xatolik yuz berdi.</p>`;
  }
}

function renderDachaDetail(dacha) {
  const content = document.getElementById('detailModalContent');
  const currencySymbol = dacha.currency === 'UZS' ? 'so\'m' : '$';

  const images = dacha.media && dacha.media.length > 0 
    ? dacha.media.map(m => m.url) 
    : ['https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1200&auto=format&fit=crop&q=80'];

  const mainImg = images[0];
  const thumb1 = images[1] || mainImg;
  const thumb2 = images[2] || thumb1;

  const today = new Date().toISOString().split('T')[0];

  content.innerHTML = `
    <div class="detail-gallery">
      <img src="${mainImg}" class="detail-main-img" id="detailMainImg" alt="${dacha.name}" />
      <div class="detail-thumb-grid">
        <img src="${thumb1}" alt="Photo 2" onclick="switchMainImage('${thumb1}')" />
        <img src="${thumb2}" alt="Photo 3" onclick="switchMainImage('${thumb2}')" />
      </div>
    </div>

    <div class="detail-body">
      <div>
        <div style="display:flex; align-items:center; gap: 0.5rem; margin-bottom: 0.5rem;">
          <span style="background:var(--primary-light); color:var(--primary-dark); padding: 0.25rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 700;">📍 ${dacha.region}, ${dacha.district}</span>
          <span style="color:var(--text-muted); font-size:0.85rem;">${dacha.mahalla || ''} ${dacha.address || ''}</span>
        </div>

        <h2 style="font-size: 1.65rem; font-weight: 800; color: var(--dark); margin-bottom: 0.75rem;">${dacha.name}</h2>
        
        <div class="card-specs" style="font-size: 0.95rem; margin-bottom: 1.25rem;">
          <span>👥 <strong>${dacha.capacity}</strong> kishilik sig'im</span>
          <span>•</span>
          <span>🛏️ <strong>${dacha.rooms_count}</strong> ta xona</span>
        </div>

        <h4 style="font-size: 1rem; font-weight: 700; color: var(--dark); margin-top: 1.25rem;">Tavsif:</h4>
        <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.7; margin-top: 0.4rem;">
          ${dacha.description || 'Qo\'shimcha tavsif kiritilmagan.'}
        </p>

        <h4 style="font-size: 1rem; font-weight: 700; color: var(--dark); margin-top: 1.75rem;">Qulayliklar:</h4>
        <div class="amenities-list">
          ${(dacha.amenities && dacha.amenities.length > 0) ? dacha.amenities.map(a => `
            <div class="amenity-chip">
              <span>${a.icon || '✨'}</span> ${a.name}
            </div>
          `).join('') : '<span style="color:var(--text-muted);">Qulayliklar belgilanmagan</span>'}
        </div>

        ${dacha.owner ? `
          <div style="margin-top: 2rem; padding: 1.25rem; background: var(--bg-page); border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
            <img src="${dacha.owner.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100'}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" />
            <div>
              <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Dacha Egasi:</div>
              <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark);">${dacha.owner.name}</div>
              <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">📞 ${dacha.owner.phone || 'Telegram orqali bog\'lanish'}</div>
            </div>
          </div>
        ` : ''}
      </div>

      <!-- Booking Widget -->
      <div>
        <div class="booking-widget-card">
          <div class="widget-price-header">
            <div>
              <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Ish kunlari:</span>
              <div style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark);">${(parseFloat(dacha.weekday_price || dacha.default_price || 0)).toLocaleString()} <span style="font-size:0.85rem; font-weight:500;">${currencySymbol}/kun</span></div>
            </div>
            <div style="text-align: right;">
              <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Dam olish:</span>
              <div style="font-size: 1.2rem; font-weight: 800; color: var(--accent);">${(parseFloat(dacha.weekend_price || dacha.weekday_price || dacha.default_price || 0)).toLocaleString()} <span style="font-size:0.85rem; font-weight:500;">${currencySymbol}</span></div>
            </div>
          </div>

          <form id="bookingForm" onsubmit="handleBookingSubmit(event)">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
              <div class="form-group">
                <label>Kirish sanasi</label>
                <input type="date" id="bookStartDate" class="form-control" min="${today}" required onchange="calculatePrice()" />
              </div>
              <div class="form-group">
                <label>Chiqish sanasi</label>
                <input type="date" id="bookEndDate" class="form-control" min="${today}" required onchange="calculatePrice()" />
              </div>
            </div>

            <div class="form-group">
              <label>Dam oluvchilar soni</label>
              <input type="number" id="bookGuests" class="form-control" value="2" min="1" max="${dacha.capacity}" required />
            </div>

            <div class="form-group">
              <label>Izoh / Istaklar (Ixtiyoriy)</label>
              <textarea id="bookNotes" class="form-control" rows="2" placeholder="Masalan: oilaviy dam olish uchun..."></textarea>
            </div>

            <!-- Price Breakdown Calculation Box -->
            <div class="calc-box" id="calcBox" style="display: none;">
              <div class="calc-row">
                <span>Ish kunlari (<span id="calcWeekdays">0</span> ta tun):</span>
                <span id="calcWeekdayTotal">0</span>
              </div>
              <div class="calc-row">
                <span>Dam olish kunlari (<span id="calcWeekends">0</span> ta tun):</span>
                <span id="calcWeekendTotal">0</span>
              </div>
              <div class="calc-row total">
                <span>Jami to'lov:</span>
                <span id="calcFinalTotal">0</span>
              </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 1rem; margin-top: 0.5rem;">
              🚀 Band qilish so'rovi
            </button>
          </form>
        </div>
      </div>
    </div>
  `;
}

function switchMainImage(url) {
  const main = document.getElementById('detailMainImg');
  if (main) main.src = url;
}

async function loadDachaCalendar(dachaId) {
  try {
    const res = await fetch(`${API_BASE}/dachas/${dachaId}/calendar`);
    const data = await res.json();
    state.bookedDates = data.booked_dates || [];
  } catch (err) {
    console.error(err);
  }
}

async function calculatePrice() {
  const start = document.getElementById('bookStartDate').value;
  const end = document.getElementById('bookEndDate').value;
  const calcBox = document.getElementById('calcBox');

  if (!start || !end || !state.currentDacha) {
    if (calcBox) calcBox.style.display = 'none';
    return;
  }

  if (new Date(start) > new Date(end)) {
    showToast('Chiqish sanasi kirish sanasidan keyin bo\'lishi kerak', 'error');
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/dachas/${state.currentDacha.id}/calculate-price`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ start_date: start, end_date: end })
    });

    if (!res.ok) throw new Error('Hisoblab bo\'lmadi');
    const data = await res.json();

    const currency = data.currency === 'UZS' ? 'so\'m' : '$';

    document.getElementById('calcWeekdays').textContent = data.weekdays_count;
    document.getElementById('calcWeekdayTotal').textContent = `${(data.weekdays_count * data.weekday_price).toLocaleString()} ${currency}`;

    document.getElementById('calcWeekends').textContent = data.weekend_days_count;
    document.getElementById('calcWeekendTotal').textContent = `${(data.weekend_days_count * data.weekend_price).toLocaleString()} ${currency}`;

    document.getElementById('calcFinalTotal').textContent = `${Number(data.total_price).toLocaleString()} ${currency}`;

    calcBox.style.display = 'block';
  } catch (err) {
    console.error(err);
  }
}

async function handleBookingSubmit(e) {
  e.preventDefault();

  if (!state.token) {
    openAuthModal('Bron qilish uchun avval Telegram yoki Google orqali tizimga kiring.');
    return;
  }

  const start = document.getElementById('bookStartDate').value;
  const end = document.getElementById('bookEndDate').value;
  const guests = document.getElementById('bookGuests').value;
  const notes = document.getElementById('bookNotes').value;

  try {
    const res = await fetch(`${API_BASE}/dachas/${state.currentDacha.id}/book`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${state.token}`
      },
      body: JSON.stringify({
        start_date: start,
        end_date: end,
        guests_count: guests,
        notes: notes
      })
    });

    const result = await res.json();

    if (!res.ok) {
      showToast(result.message || 'Bron qilishda xatolik', 'error');
      return;
    }

    showToast('🎉 Bron so\'rovingiz yuborildi! Dacha egasi tez orada bog\'lanadi.', 'success');
    closeModal('detailModal');
  } catch (err) {
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

// ==========================================
// OWNER DASHBOARD & CREATE DACHA
// ==========================================

function renderAmenitiesForOwnerForm(amenities) {
  const container = document.getElementById('amenitiesCheckboxes');
  if (!container) return;

  container.innerHTML = amenities.map(a => `
    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; background: var(--bg-page); padding: 0.4rem 0.65rem; border-radius: var(--radius-sm); border: 1px solid var(--border); cursor: pointer;">
      <input type="checkbox" name="amenities[]" value="${a.id}" />
      <span>${a.icon || '✨'}</span> ${a.name}
    </label>
  `).join('');
}

async function handleCreateDacha(e) {
  e.preventDefault();

  if (!state.token) {
    openAuthModal('Dacha e\'lonini joylash uchun avval dacha egasi sifatida ro\'yxatdan o\'ting.');
    return;
  }

  const form = document.getElementById('createDachaForm');
  const formData = new FormData(form);

  const btn = form.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.textContent = 'Yuklanmoqda...';

  try {
    const res = await fetch(`${API_BASE}/owner/dachas`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`
      },
      body: formData
    });

    const result = await res.json();

    if (!res.ok) {
      showToast(result.message || 'Xatolik yuz berdi', 'error');
      btn.disabled = false;
      btn.textContent = 'E\'lonni joylash';
      return;
    }

    showToast('🎉 Yangi dacha e\'loningiz muvaffaqiyatli joylandi!', 'success');
    form.reset();
    closeModal('ownerModal');
    loadDachas();
  } catch (err) {
    showToast('Fayllarni yuklashda xatolik', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'E\'lonni joylash';
  }
}

// ==========================================
// AUTH & MODALS
// ==========================================

function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('open');
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('open');
}

function openAuthModal(notice = '') {
  const noticeEl = document.getElementById('authNotice');
  if (noticeEl) {
    noticeEl.textContent = notice;
    noticeEl.style.display = notice ? 'block' : 'none';
  }
  openModal('authModal');
}

function loginAsDemoOwner() {
  // Demo uchun to'g'ridan-to'g'ri sinab ko'rish imkoni
  state.token = 'demo_token_123';
  state.user = {
    name: 'Alisher Rahimov',
    role: 'owner',
    phone: '+998901234567'
  };
  localStorage.setItem('oromgo_token', state.token);
  localStorage.setItem('oromgo_user', JSON.stringify(state.user));
  updateAuthUI();
  closeModal('authModal');
  showToast('Dacha egasi sifatida tizimga kirdingiz!', 'success');
}

function updateAuthUI() {
  const userBox = document.getElementById('navUserBox');
  if (!userBox) return;

  if (state.user && state.token) {
    userBox.innerHTML = `
      <div style="display: flex; align-items: center; gap: 0.65rem;">
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--dark);">👤 ${state.user.name.split(' ')[0]}</span>
        <button class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="logout()">Chiqish</button>
      </div>
    `;
  } else {
    userBox.innerHTML = `
      <button class="btn btn-outline" onclick="openAuthModal()">Kirish</button>
    `;
  }
}

function logout() {
  state.token = '';
  state.user = null;
  localStorage.removeItem('oromgo_token');
  localStorage.removeItem('oromgo_user');
  updateAuthUI();
  showToast('Tizimdan chiqdingiz.', 'success');
}

function resetSearch() {
  const form = document.getElementById('searchForm');
  if (form) form.reset();
  loadDachas();
}

function filterByCategory(btn, category) {
  document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  if (category === 'all') {
    loadDachas();
  } else {
    // Filtr parametrlarini o'rnatish
    loadDachas();
  }
}

// ==========================================
// TOAST NOTIFICATIONS
// ==========================================

function showToast(message, type = 'success') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 4000);
}

// Helper animations
const style = document.createElement('style');
style.textContent = `@keyframes spin { to { transform: rotate(360deg); } }`;
document.head.appendChild(style);
