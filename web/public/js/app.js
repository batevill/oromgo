/**
 * OROMGO - FRONTEND APP CLIENT LOGIC (VANILLA JS + LEAFLET MAP)
 */

const API_BASE = '/api';

const state = {
  dachas: [],
  amenities: [],
  locations: {},
  activeFilter: 'all',
  currentDacha: null,
  currentReviews: [],
  bookedDates: [],
  favoriteIds: [],
  pendingFavorites: new Set(),
  selectedRating: 5,
  isMapView: false,
  mapInstance: null,
  markersLayer: null,
  notifications: [],
  unreadNotificationsCount: 0,
  hasTelegramLinked: false,
  notifFilter: 'all',
  pendingDachaId: null,
  pendingAction: null,
  ownerDachas: [],
  ownerBookings: [],
  activeCabinetTab: 'dachas',
  adminDachas: [],
  adminFilter: 'all',
  adminSearchQuery: '',
  adminStats: {},
  token: localStorage.getItem('oromgo_token') || '',
  user: JSON.parse(localStorage.getItem('oromgo_user') || 'null'),
};

// ==========================================
// INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  // Check if redirected from OAuth (Telegram/Google)
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('auth_token')) {
    state.token = urlParams.get('auth_token');
    state.user = {
      id: urlParams.get('user_id'),
      name: urlParams.get('user_name') || 'Foydalanuvchi',
      role: urlParams.get('user_role') || 'user'
    };
    localStorage.setItem('oromgo_token', state.token);
    localStorage.setItem('oromgo_user', JSON.stringify(state.user));
    window.history.replaceState({}, document.title, window.location.pathname);
    showToast(`Xush kelibsiz, ${state.user.name}!`, 'success');

    if (state.user.role === 'admin' || state.user.role === 'super_admin') {
      window.location.href = '/admin';
      return;
    }
  }

  initApp();
  setupEventListeners();
  updateAuthUI();
  
  // Polling for live notifications every 20 seconds
  setInterval(() => {
    if (state.token) {
      loadNotifications(false);
    }
  }, 20000);
});

async function initApp() {
  await Promise.all([loadLocations(), loadAmenities(), loadFavorites(), loadNotifications()]);
  await loadDachas();
}

function setupEventListeners() {
  // Search Form
  const searchForm = document.getElementById('searchForm');
  if (searchForm) {
    searchForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const region = document.getElementById('searchRegion')?.value;
      const district = document.getElementById('searchDistrict')?.value;
      const capacity = document.getElementById('searchCapacity')?.value;
      const currency = document.getElementById('searchCurrency')?.value;
      const maxPrice = document.getElementById('searchPrice')?.value;

      const params = {};
      if (region) params.region = region;
      if (district) params.district = district;
      if (capacity) params.capacity = capacity;
      if (currency) params.currency = currency;
      if (maxPrice) params.max_price = maxPrice;

      loadDachas(params);
    });
  }

  // Region dropdown change listeners
  const searchRegion = document.getElementById('searchRegion');
  if (searchRegion) {
    searchRegion.addEventListener('change', () => {
      populateDistrictsForSelect('searchDistrict', searchRegion.value, 'Barcha tumanlar');
    });
  }

  const ownerRegion = document.getElementById('ownerRegion');
  if (ownerRegion) {
    ownerRegion.addEventListener('change', () => {
      populateDistrictsForSelect('ownerDistrict', ownerRegion.value, 'Tumanni tanlang');
    });
  }

  const editRegion = document.getElementById('editRegion');
  if (editRegion) {
    editRegion.addEventListener('change', () => {
      populateDistrictsForSelect('editDistrict', editRegion.value, 'Tumanni tanlang');
    });
  }

  // Owner Dacha Create Form Submit
  const createDachaForm = document.getElementById('createDachaForm');
  if (createDachaForm) {
    createDachaForm.addEventListener('submit', handleCreateDacha);
  }

  // Owner Dacha Edit Form Submit
  const editDachaForm = document.getElementById('editDachaForm');
  if (editDachaForm) {
    editDachaForm.addEventListener('submit', handleUpdateDacha);
  }

  // Owner Block Dates Form Submit
  const ownerBlockDatesForm = document.getElementById('ownerBlockDatesForm');
  if (ownerBlockDatesForm) {
    ownerBlockDatesForm.addEventListener('submit', handleBlockDatesSubmit);
  }
}

// ==========================================
// API CALLS & RENDERING
// ==========================================

async function loadLocations() {
  try {
    const res = await fetch(`${API_BASE}/locations`);
    if (!res.ok) throw new Error('Hududlar ma\'lumotlarini yuklab bo\'lmadi');
    const data = await res.json();
    state.locations = data || {};
    populateRegionSelects();
  } catch (err) {
    console.error('Locations error:', err);
  }
}

function populateRegionSelects() {
  const regions = Object.keys(state.locations);

  // Search Region select
  const searchRegSelect = document.getElementById('searchRegion');
  if (searchRegSelect) {
    searchRegSelect.innerHTML = '<option value="">Barcha viloyatlar</option>' +
      regions.map(r => `<option value="${r}">${r}</option>`).join('');
  }

  // Owner Form Region select
  const ownerRegSelect = document.getElementById('ownerRegion');
  if (ownerRegSelect) {
    ownerRegSelect.innerHTML = '<option value="">Viloyatni tanlang</option>' +
      regions.map(r => `<option value="${r}">${r}</option>`).join('');
  }

  // Edit Form Region select
  const editRegSelect = document.getElementById('editRegion');
  if (editRegSelect) {
    editRegSelect.innerHTML = '<option value="">Viloyatni tanlang</option>' +
      regions.map(r => `<option value="${r}">${r}</option>`).join('');
  }
}

function populateDistrictsForSelect(selectId, selectedRegion, placeholder = 'Barcha tumanlar') {
  const selectEl = document.getElementById(selectId);
  if (!selectEl) return;

  if (!selectedRegion || !state.locations[selectedRegion]) {
    selectEl.innerHTML = `<option value="">${placeholder}</option>`;
    return;
  }

  const districts = Object.keys(state.locations[selectedRegion] || {});
  selectEl.innerHTML = `<option value="">${placeholder}</option>` +
    districts.map(d => `<option value="${d}">${d}</option>`).join('');
}

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

async function loadFavorites() {
  if (!state.token) {
    state.favoriteIds = [];
    return;
  }
  try {
    const res = await fetch(`${API_BASE}/favorites`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      const data = await res.json();
      state.favoriteIds = (data.favorite_ids || []).map(id => Number(id));
    }
  } catch (err) {
    console.error('Favorites error:', err);
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
    updateMapMarkers(state.dachas);
  } catch (err) {
    grid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: #ef4444; font-weight: 600;">Ma'lumotlarni yuklashda xatolik yuz berdi.</p>`;
  }
}

function renderDachas(dachas) {
  const grid = document.getElementById('dachaGrid');
  const countEl = document.getElementById('resultsCount');
  const titleEl = document.getElementById('sectionTitle');

  if (state.activeFilter === 'favorites') {
    if (titleEl) titleEl.textContent = '❤️ Sevimli dachalarim';
    if (countEl) countEl.textContent = `${dachas.length} ta saqlangan dacha`;
  } else {
    if (titleEl) titleEl.textContent = 'Tavsiya etiladigan dachalar';
    if (countEl) countEl.textContent = `${dachas.length} ta dacha topildi`;
  }

  if (!dachas.length) {
    if (state.activeFilter === 'favorites') {
      grid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
          <div style="font-size: 3rem; margin-bottom: 0.5rem;">💔</div>
          <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hozircha sevimlilar yo'q</h3>
          <p style="color: var(--text-muted); font-size: 0.95rem;">Yoqqan dachalar ustidagi yurakcha (❤️) tugmasini bosib saqlang.</p>
          <button class="btn btn-outline" style="margin-top: 1.25rem;" onclick="resetSearch()">Barcha dachalarni ko'rish</button>
        </div>
      `;
    } else {
      grid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
          <div style="font-size: 3rem; margin-bottom: 0.5rem;">🏡</div>
          <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Mos dacha topilmadi</h3>
          <p style="color: var(--text-muted); font-size: 0.95rem;">Qidiruv parametrlarini o'zgartirib ko'ring yoki barcha dachalarni ko'ring.</p>
          <button class="btn btn-outline" style="margin-top: 1.25rem;" onclick="resetSearch()">Filtrlarni tozalash</button>
        </div>
      `;
    }
    return;
  }

  grid.innerHTML = dachas.map(dacha => {
    const firstImg = dacha.media && dacha.media.length > 0 
      ? dacha.media[0].url 
      : '/storage/dachas/images/dacha_1_1.jpg';

    const currencySymbol = dacha.currency === 'UZS' ? 'so\'m' : '$';
    const weekdayPrice = parseFloat(dacha.weekday_price || dacha.default_price || 0);
    const weekendPrice = parseFloat(dacha.weekend_price || dacha.weekday_price || dacha.default_price || 0);
    const avgRating = dacha.avg_rating ? parseFloat(dacha.avg_rating).toFixed(1) : '5.0';
    const reviewsCount = dacha.reviews_count || 0;
    const isFav = state.favoriteIds.includes(Number(dacha.id));

    return `
      <div class="dacha-card" id="dachaCard-${dacha.id}" data-dacha-id="${dacha.id}" onclick="openDachaDetail(${dacha.id})">
        <div class="card-img-wrapper">
          <img src="${firstImg}" alt="${dacha.name}" loading="lazy" />
          
          <div class="card-badge-rating">
            ⭐ ${avgRating} <span style="font-weight: 500; font-size: 0.7rem; color: var(--text-muted);">(${reviewsCount})</span>
          </div>

          <button class="card-btn-favorite ${isFav ? 'active' : ''}" data-id="${dacha.id}" onclick="toggleFavorite(event, ${dacha.id})">
            ${isFav ? '❤️' : '🤍'}
          </button>

          <div class="card-badge-location">
            📍 ${dacha.region || ''}, ${dacha.district || ''}
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
          <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">📅 Bandlik taqvimi</span>
            <button class="btn btn-primary" style="padding: 0.35rem 0.85rem; font-size: 0.85rem; font-weight: 700; border-radius: var(--radius-sm);" onclick="event.stopPropagation(); openDachaDetail(${dacha.id});">
              Batafsil ➔
            </button>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// ==========================================
// INTERACTIVE MAP (LEAFLET + AIRBNB STYLE)
// ==========================================

function initMainMap() {
  if (state.mapInstance) return;

  // Center on Tashkent region / Chorvoq
  state.mapInstance = L.map('mainMap').setView([41.55, 69.95], 9);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(state.mapInstance);

  state.markersLayer = L.layerGroup().addTo(state.mapInstance);
}

function updateMapMarkers(dachas) {
  if (!state.mapInstance) initMainMap();
  if (!state.markersLayer) return;

  state.markersLayer.clearLayers();

  const bounds = [];

  dachas.forEach(dacha => {
    const lat = parseFloat(dacha.latitude);
    const lng = parseFloat(dacha.longitude);

    if (isNaN(lat) || isNaN(lng)) return;

    bounds.push([lat, lng]);

    const weekdayPrice = parseFloat(dacha.weekday_price || dacha.default_price || 0);
    const badgeText = dacha.currency === 'UZS' 
      ? `${(weekdayPrice / 1000000).toFixed(1)}M so'm` 
      : `$${weekdayPrice}`;

    const icon = L.divIcon({
      className: 'custom-leaflet-marker',
      html: `<div class="map-price-badge">${badgeText}</div>`,
      iconSize: [80, 30],
      iconAnchor: [40, 15]
    });

    const marker = L.marker([lat, lng], { icon }).addTo(state.markersLayer);

    const firstImg = dacha.media && dacha.media.length > 0 
      ? dacha.media[0].url 
      : '/storage/dachas/images/dacha_1_1.jpg';

    const avgRating = dacha.avg_rating ? parseFloat(dacha.avg_rating).toFixed(1) : '5.0';

    const popupHtml = `
      <div class="map-popup-card" onclick="openDachaDetail(${dacha.id})">
        <img src="${firstImg}" alt="${dacha.name}" />
        <div class="map-popup-info">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.25rem;">
            <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">📍 ${dacha.district || dacha.region}</span>
            <span style="font-size:0.8rem; font-weight:700; color:#b45309;">⭐ ${avgRating}</span>
          </div>
          <div class="map-popup-title">${dacha.name}</div>
          <div class="map-popup-price">${badgeText} <span style="font-size:0.75rem; font-weight:500; color:var(--text-muted);">/kun</span></div>
        </div>
      </div>
    `;

    marker.bindPopup(popupHtml, { maxWidth: 280 });
  });

  if (bounds.length > 0 && state.isMapView) {
    state.mapInstance.fitBounds(bounds, { padding: [50, 50] });
  }
}

function toggleViewMode() {
  state.isMapView = !state.isMapView;

  const grid = document.getElementById('dachaGrid');
  const mapContainer = document.getElementById('mapViewContainer');
  const btnIcon = document.getElementById('mapBtnIcon');
  const btnText = document.getElementById('mapBtnText');

  if (state.isMapView) {
    grid.style.display = 'none';
    mapContainer.style.display = 'block';
    btnIcon.textContent = '📋';
    btnText.textContent = 'Ro\'yxatni ko\'rsatish';

    initMainMap();
    setTimeout(() => {
      state.mapInstance.invalidateSize();
      updateMapMarkers(state.dachas);
    }, 200);
  } else {
    grid.style.display = 'grid';
    mapContainer.style.display = 'none';
    btnIcon.textContent = '🗺️';
    btnText.textContent = 'Xaritani ko\'rsatish';
  }
}

// ==========================================
// FAVORITES (WISHLIST) - OPTIMISTIC REAL-TIME UI
// ==========================================

function updateFavoriteButtonsDOM(numericId, isFav) {
  document.querySelectorAll(`.card-btn-favorite[data-id="${numericId}"]`).forEach(btn => {
    if (btn.innerText && (btn.innerText.includes('Saqlash') || btn.innerText.includes('Saqlangan'))) {
      btn.innerHTML = isFav ? '❤️ Saqlangan' : '🤍 Saqlash';
    } else {
      btn.innerHTML = isFav ? '❤️' : '🤍';
    }
    btn.classList.toggle('active', isFav);
  });
}

function rollbackFavorite(numericId, previousIsFav) {
  if (previousIsFav) {
    if (!state.favoriteIds.includes(numericId)) state.favoriteIds.push(numericId);
  } else {
    state.favoriteIds = state.favoriteIds.filter(id => id !== numericId);
  }
  updateFavoriteButtonsDOM(numericId, previousIsFav);
}

function toggleFavorite(e, dachaId) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }

  const numericId = Number(dachaId);

  // Double click / race condition oldini olish
  if (state.pendingFavorites.has(numericId)) {
    return;
  }

  if (!state.token) {
    openAuthModal('Dachani sevimlilar ro\'yxatiga qo\'shish uchun avval tizimga kiring.');
    return;
  }

  state.pendingFavorites.add(numericId);

  // Tugmalarni vaqtincha bosilmaydigan qilish (Visual lock)
  document.querySelectorAll(`.card-btn-favorite[data-id="${numericId}"]`).forEach(btn => {
    btn.style.pointerEvents = 'none';
  });

  // 1. DARHOL (REAL-TIME OPTIMISTIC UI) YANGILASH
  const currentlyFavorite = state.favoriteIds.includes(numericId);
  const newFavoriteState = !currentlyFavorite;

  if (newFavoriteState) {
    if (!state.favoriteIds.includes(numericId)) state.favoriteIds.push(numericId);
    showToast('❤️ Sevimlilar ro\'yxatiga saqlandi!', 'success');
  } else {
    state.favoriteIds = state.favoriteIds.filter(id => id !== numericId);
    showToast('Dacha sevimlilardan o\'chirildi.', 'info');
  }

  // Yurakcha tugmalarini darhol yangilash
  updateFavoriteButtonsDOM(numericId, newFavoriteState);

  // Agar hozir "❤️ Sevimlilarim" filtri sahifasida bo'lsa va dacha sevimlilardan o'chirilsa:
  if (state.activeFilter === 'favorites' && !newFavoriteState) {
    const card = document.getElementById(`dachaCard-${numericId}`) || document.querySelector(`.dacha-card[data-dacha-id="${numericId}"]`);
    if (card) {
      card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
      card.style.opacity = '0';
      card.style.transform = 'scale(0.9)';
      setTimeout(() => {
        card.style.display = 'none';
        
        // Qolgan ko'rinib turgan dachalar sonini hisoblash
        const remainingCards = Array.from(document.querySelectorAll('#dachaGrid .dacha-card')).filter(c => c.style.display !== 'none');
        const countEl = document.getElementById('resultsCount');
        if (countEl) countEl.textContent = `${remainingCards.length} ta saqlangan dacha`;

        if (remainingCards.length === 0) {
          const grid = document.getElementById('dachaGrid');
          if (grid) {
            grid.innerHTML = `
              <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">💔</div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hozircha sevimlilar yo'q</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Yoqqan dachalar ustidagi yurakcha (❤️) tugmasini bosib saqlang.</p>
                <button class="btn btn-outline" style="margin-top: 1.25rem;" onclick="resetSearch()">Barcha dachalarni ko'rish</button>
              </div>
            `;
          }
        }
      }, 300);
    }
  }

  // 2. ORQA FONDA ASINXRON REQUEST YUBORISH (Background Sync)
  fetch(`${API_BASE}/favorites/${numericId}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${state.token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  })
  .then(async (res) => {
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      rollbackFavorite(numericId, currentlyFavorite);
      showToast(err.message || 'Xatolik yuz berdi', 'error');
    }
  })
  .catch((err) => {
    console.error('Favorite background sync error:', err);
    rollbackFavorite(numericId, currentlyFavorite);
    showToast('Server bilan bog\'lanishda xatolik yuz berdi', 'error');
  })
  .finally(() => {
    state.pendingFavorites.delete(numericId);
    document.querySelectorAll(`.card-btn-favorite[data-id="${numericId}"]`).forEach(btn => {
      btn.style.pointerEvents = 'auto';
    });
  });
}

async function showFavoritesOnly(btn) {
  document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
  const favPill = btn || document.querySelector('.pill-btn[onclick*="favorites"]');
  if (favPill) favPill.classList.add('active');

  if (!state.token) {
    openAuthModal('Saqlangan dachalaringizni ko\'rish uchun iltimos, tizimga kiring.');
    return;
  }

  const grid = document.getElementById('dachaGrid');
  grid.innerHTML = `
    <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
      <div style="display:inline-block; width: 40px; height: 40px; border: 4px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top: 1rem; color: var(--text-muted); font-weight: 600;">Sevimlilar yuklanmoqda...</p>
    </div>
  `;

  try {
    const res = await fetch(`${API_BASE}/favorites`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });
    const data = await res.json();
    const favDachas = data.favorites?.data || [];
    state.favoriteIds = (data.favorite_ids || []).map(id => Number(id));

    const countEl = document.getElementById('resultsCount');
    if (countEl) countEl.textContent = `${favDachas.length} ta saqlangan dacha`;

    if (!favDachas.length) {
      grid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 4rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
          <div style="font-size: 3rem; margin-bottom: 0.5rem;">💔</div>
          <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hozircha sevimlilar yo'q</h3>
          <p style="color: var(--text-muted); font-size: 0.95rem;">Yoqqan dachalar ustidagi yurakcha (❤️) tugmasini bosib saqlang.</p>
          <button class="btn btn-outline" style="margin-top: 1.25rem;" onclick="resetSearch()">Barcha dachalarni ko'rish</button>
        </div>
      `;
      return;
    }

    renderDachas(favDachas);
  } catch (err) {
    grid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: red;">Sevimlilarni yuklab bo'lmadi.</p>`;
  }
}

// ==========================================
// DACHA DETAIL, CALENDAR & REVIEWS MODAL
// ==========================================

async function openDachaDetail(id) {
  // If user is not logged in, prompt authentication and remember which dacha they wanted to view
  if (!state.token) {
    state.pendingDachaId = id;
    openAuthModal('Dacha haqida to\'liq ma\'lumot, fotosuratlar, band kunlar taqvimi va dacha egasining kontaktlarini ko\'rish uchun iltimos, avval tizimga kiring.');
    return;
  }

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
    const headers = {
      'Authorization': `Bearer ${state.token}`,
      'Accept': 'application/json'
    };

    const [dachaRes, reviewsRes] = await Promise.all([
      fetch(`${API_BASE}/dachas/${id}`, { credentials: 'omit', headers }),
      fetch(`${API_BASE}/dachas/${id}/reviews`, { headers: { 'Accept': 'application/json' } })
    ]);
    
    // In case token is expired or invalid
    if (dachaRes.status === 401) {
      state.token = '';
      state.user = null;
      localStorage.removeItem('oromgo_token');
      localStorage.removeItem('oromgo_user');
      updateAuthUI();
      closeModal('detailModal');
      state.pendingDachaId = id;
      openAuthModal('Sessiyangiz muddati tugagan. Dacha ma\'lumotlarini ko\'rish uchun iltimos, tizimga qayta kiring.');
      return;
    }

    if (!dachaRes.ok) {
      throw new Error(`Server xatosi: ${dachaRes.status}`);
    }

    const dacha = await dachaRes.json();
    const reviewsData = reviewsRes.ok ? await reviewsRes.json() : { reviews: [], avg_rating: 5.0, total_reviews: 0 };

    state.currentDacha = dacha;
    state.currentReviews = reviewsData.reviews || [];

    loadDachaCalendar(id);
    renderDachaDetail(dacha, reviewsData);
  } catch (err) {
    console.error('Dacha tafsilotlarini yuklashda xatolik:', err);
    content.innerHTML = `
      <div style="text-align: center; padding: 3rem 1rem;">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">⚠️</div>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Dacha ma'lumotlarini yuklashda xatolik yuz berdi</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.25rem;">Qaytadan urinib ko'ring yoki tizimga qayta kiring.</p>
        <div style="display: flex; justify-content: center; gap: 0.75rem;">
          <button class="btn btn-outline" onclick="closeModal('detailModal')">Yopish</button>
          <button class="btn btn-primary" onclick="openDachaDetail(${id})">Qayta yuklash</button>
        </div>
      </div>
    `;
  }
}

function renderDachaDetail(dacha, reviewsData = { reviews: [], avg_rating: 5.0, total_reviews: 0 }) {
  const content = document.getElementById('detailModalContent');
  const currencySymbol = dacha.currency === 'UZS' ? 'so\'m' : '$';

  const images = dacha.media && dacha.media.length > 0 
    ? dacha.media.map(m => m.url) 
    : ['/storage/dachas/images/dacha_1_1.jpg'];

  const mainImg = images[0];
  const thumb1 = images[1] || mainImg;
  const thumb2 = images[2] || thumb1;

  const today = new Date().toISOString().split('T')[0];
  const weekdayPrice = parseFloat(dacha.weekday_price || dacha.default_price || 0);
  const weekendPrice = parseFloat(dacha.weekend_price || dacha.weekday_price || dacha.default_price || 0);

  const reviews = reviewsData.reviews || [];
  const avgRating = reviewsData.avg_rating || 5.0;
  const totalReviews = reviewsData.total_reviews || reviews.length;

  const isFavDetail = state.favoriteIds.includes(Number(dacha.id));

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
        <div style="display:flex; align-items:center; justify-content:space-between; gap: 0.5rem; margin-bottom: 0.5rem;">
          <div style="display:flex; align-items:center; gap: 0.5rem;">
            <span style="background:var(--primary-light); color:var(--primary-dark); padding: 0.25rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 700;">📍 ${dacha.region}, ${dacha.district}</span>
            <span style="color:var(--text-muted); font-size:0.85rem;">${dacha.mahalla || ''} ${dacha.address || ''}</span>
          </div>
          <button class="card-btn-favorite ${isFavDetail ? 'active' : ''}" data-id="${dacha.id}" onclick="toggleFavorite(event, ${dacha.id})" style="position:static; width:auto; height:auto; padding: 0.35rem 0.85rem; font-size:0.85rem; border-radius:var(--radius-full); background:white; border:1px solid var(--border); box-shadow:none;">
            ${isFavDetail ? '❤️ Saqlangan' : '🤍 Saqlash'}
          </button>
        </div>

        <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 0.5rem;">
          <h2 style="font-size: 1.65rem; font-weight: 800; color: var(--dark);">${dacha.name}</h2>
          <div style="font-size: 1.1rem; font-weight: 800; color: #b45309; background: var(--accent-light); padding: 0.2rem 0.6rem; border-radius: var(--radius-sm);">
            ⭐ ${avgRating} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">(${totalReviews})</span>
          </div>
        </div>
        
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

        <!-- Reviews & Ratings Block -->
        <div class="reviews-section">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark);">
              ⭐ Sharhlar va Baholar (${totalReviews})
            </h3>
          </div>

          <!-- Reviews List -->
          <div id="reviewsList">
            ${reviews.length > 0 ? reviews.map(r => `
              <div class="review-card">
                <div class="review-header">
                  <div class="reviewer-info">
                    <img src="${r.user?.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100'}" class="reviewer-avatar" />
                    <div>
                      <div style="font-weight: 700; font-size: 0.9rem; color: var(--dark);">${r.user?.name || 'Mijoz'}</div>
                      <div style="font-size: 0.75rem; color: var(--text-muted);">${new Date(r.created_at).toLocaleDateString('uz-UZ')}</div>
                    </div>
                  </div>
                  <div class="star-rating">
                    ${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}
                  </div>
                </div>
                <p style="font-size: 0.9rem; color: var(--dark-light); line-height: 1.5;">${r.comment}</p>
              </div>
            `).join('') : '<p style="color: var(--text-muted); font-size: 0.9rem; padding: 1rem 0;">Hozircha sharhlar mavjud emas. Birinchi bo\'lib o\'z fikringizni qoldiring!</p>'}
          </div>

          <!-- Add Review Form -->
          <div style="margin-top: 1.5rem; background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem;">
            <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--dark); margin-bottom: 0.5rem;">✍️ Dacha haqida fikringizni qoldiring</h4>
            <form id="reviewForm" onsubmit="handleReviewSubmit(event)">
              <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">Bahoyingizni tanlang:</label>
              <div class="star-select-group" id="starSelector">
                <span onclick="setRating(1)" class="active">★</span>
                <span onclick="setRating(2)" class="active">★</span>
                <span onclick="setRating(3)" class="active">★</span>
                <span onclick="setRating(4)" class="active">★</span>
                <span onclick="setRating(5)" class="active">★</span>
              </div>
              <textarea id="reviewComment" class="form-control" rows="2" placeholder="Dacha tozaligi, sharoitlari va taassurotlaringiz..." required></textarea>
              <button type="submit" class="btn btn-outline" style="margin-top: 0.75rem; width: 100%;">
                Sharhni yuborish
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Booking Widget -->
      <div>
        <div class="booking-widget-card">
          <div class="widget-price-header">
            <div>
              <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Ish kunlari:</span>
              <div style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark);">${weekdayPrice.toLocaleString()} <span style="font-size:0.85rem; font-weight:500;">${currencySymbol}/kun</span></div>
            </div>
            <div style="text-align: right;">
              <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Dam olish:</span>
              <div style="font-size: 1.2rem; font-weight: 800; color: var(--accent);">${weekendPrice.toLocaleString()} <span style="font-size:0.85rem; font-weight:500;">${currencySymbol}</span></div>
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

function setRating(rating) {
  state.selectedRating = rating;
  const stars = document.querySelectorAll('#starSelector span');
  stars.forEach((star, index) => {
    if (index < rating) {
      star.classList.add('active');
    } else {
      star.classList.remove('active');
    }
  });
}

async function handleReviewSubmit(e) {
  e.preventDefault();

  if (!state.token) {
    openAuthModal('Sharh qoldirish uchun avval tizimga kiring.');
    return;
  }

  const comment = document.getElementById('reviewComment').value;
  const rating = state.selectedRating || 5;

  try {
    const res = await fetch(`${API_BASE}/dachas/${state.currentDacha.id}/reviews`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${state.token}`
      },
      body: JSON.stringify({
        rating: rating,
        comment: comment
      })
    });

    const result = await res.json();

    if (!res.ok) {
      showToast(result.message || 'Sharh qoldirishda xatolik', 'error');
      return;
    }

    showToast('🎉 Sharhingiz qabul qilindi! Rahmat.', 'success');
    document.getElementById('reviewComment').value = '';
    
    openDachaDetail(state.currentDacha.id);
  } catch (err) {
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
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

function openOwnerModal() {
  if (!state.token) {
    state.pendingAction = 'open_owner_modal';
    openAuthModal('Dacha e\'lonini joylash uchun avval dacha egasi sifatida tizimga kiring.');
    return;
  }

  if (state.user && state.user.role !== 'owner' && state.user.role !== 'admin' && state.user.role !== 'super_admin') {
    state.pendingAction = 'open_owner_modal';
    openAuthModal('E\'lon joylash uchun dacha egasi (owner) hisobi kerak. Iltimos, dacha egasi sifatida kiring yoki demo orqali sinab ko\'ring.');
    return;
  }

  populateRegionSelects();
  openModal('ownerModal');
}

async function handleCreateDacha(e) {
  e.preventDefault();

  if (!state.token) {
    state.pendingAction = 'open_owner_modal';
    openAuthModal('Dacha e\'lonini joylash uchun avval dacha egasi sifatida ro\'yxatdan o\'ting.');
    return;
  }

  const form = document.getElementById('createDachaForm');
  const formData = new FormData(form);

  const btn = form.querySelector('button[type="submit"]');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.textContent = 'Yuklanmoqda...';

  try {
    const res = await fetch(`${API_BASE}/owner/dachas`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    const result = await res.json();

    if (!res.ok) {
      let errMsg = result.message || 'Xatolik yuz berdi';
      if (result.errors) {
        errMsg = Object.values(result.errors).flat().join('<br>');
      }
      showToast(errMsg, 'error');
      return;
    }

    showToast('🎉 Yangi dacha e\'loningiz qabul qilindi va moderatsiyaga yuborildi! Administrator tasdiqlagach, saytda ko\'rinadi.', 'success');
    form.reset();
    closeModal('ownerModal');
    loadDachas();
  } catch (err) {
    console.error('handleCreateDacha error:', err);
    showToast('Fayllarni yuklashda yoki server bilan bog\'lanishda xatolik', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalText;
  }
}

// ==========================================
// OWNER CABINET & DASHBOARD LOGIC
// ==========================================

async function openOwnerCabinetModal(tab = 'dachas') {
  if (!state.token) {
    state.pendingAction = 'open_owner_cabinet';
    openAuthModal('Dacha egasi kabinetiga kirish uchun iltimos, avval tizimga kiring.');
    return;
  }

  if (state.user && state.user.role !== 'owner' && state.user.role !== 'admin' && state.user.role !== 'super_admin') {
    state.pendingAction = 'open_owner_cabinet';
    openAuthModal('Kabinetga kirish uchun dacha egasi (owner) huquqi talab qilinadi. Iltimos, dacha egasi sifatida kiring yoki demo orqali sinab ko\'ring.');
    return;
  }

  openModal('ownerCabinetModal');
  switchCabinetTab(tab);
  await Promise.all([loadOwnerDachas(), loadOwnerBookings(), loadOwnerReports()]);
}

function switchCabinetTab(tab) {
  state.activeCabinetTab = tab;

  // Update tab buttons
  document.getElementById('tabOwnerDachasBtn')?.classList.toggle('active', tab === 'dachas');
  document.getElementById('tabOwnerBookingsBtn')?.classList.toggle('active', tab === 'bookings');
  document.getElementById('tabOwnerReportsBtn')?.classList.toggle('active', tab === 'reports');
  document.getElementById('tabOwnerBlockDatesBtn')?.classList.toggle('active', tab === 'blockDates');

  // Update tab views
  const tabDachas = document.getElementById('cabinetTabDachas');
  const tabBookings = document.getElementById('cabinetTabBookings');
  const tabReports = document.getElementById('cabinetTabReports');
  const tabBlockDates = document.getElementById('cabinetTabBlockDates');

  if (tabDachas) tabDachas.style.display = tab === 'dachas' ? 'block' : 'none';
  if (tabBookings) tabBookings.style.display = tab === 'bookings' ? 'block' : 'none';
  if (tabReports) tabReports.style.display = tab === 'reports' ? 'block' : 'none';
  if (tabBlockDates) tabBlockDates.style.display = tab === 'blockDates' ? 'block' : 'none';

  if (tab === 'reports') {
    loadOwnerReports();
  }
}

async function loadOwnerReports(period = 'this_month') {
  const container = document.getElementById('ownerReportsContainer');
  if (!container) return;

  container.innerHTML = `
    <div style="text-align: center; padding: 3rem;">
      <div style="display:inline-block; width: 35px; height: 35px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top: 0.75rem; color: var(--text-muted); font-size: 0.9rem;">Moliyaviy hisobotlar hisoblanmoqda...</p>
    </div>
  `;

  try {
    const res = await fetch(`${API_BASE}/owner/reports?period=${period}`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    if (res.ok) {
      const report = await res.json();
      renderOwnerReports(report);
    } else {
      container.innerHTML = `<p style="color: red; text-align: center; padding: 2rem;">Hisobotlarni yuklashda xatolik.</p>`;
    }
  } catch (err) {
    console.error('loadOwnerReports error:', err);
    container.innerHTML = `<p style="color: red; text-align: center; padding: 2rem;">Server bilan bog'lanishda xatolik.</p>`;
  }
}

function renderOwnerReports(report) {
  const container = document.getElementById('ownerReportsContainer');
  if (!container) return;

  const sum = report.summary || {};
  const sources = report.sources || [];
  const monthly = report.monthly_trend || [];
  const dachasBreakdown = report.dachas_breakdown || [];

  container.innerHTML = `
    <div>
      <!-- Header & Filter -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0;">📊 Moliyaviy va Bandlik Hisoboti</h3>
          <p style="font-size: 0.825rem; color: var(--text-muted); margin: 0.2rem 0 0;">Davr: <strong>${escapeHtml(report.period_label || '')}</strong></p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Davr:</label>
          <select class="form-control" style="width: auto; padding: 0.4rem 0.8rem; font-size: 0.85rem; font-weight: 700;" onchange="loadOwnerReports(this.value)">
            <option value="this_month" ${report.period === 'this_month' ? 'selected' : ''}>Shu oy</option>
            <option value="last_month" ${report.period === 'last_month' ? 'selected' : ''}>O'tgan oy</option>
            <option value="this_year" ${report.period === 'this_year' ? 'selected' : ''}>Shu yil</option>
            <option value="all" ${report.period === 'all' ? 'selected' : ''}>Barcha vaqt</option>
          </select>
        </div>
      </div>

      <!-- Stat Cards Grid -->
      <div class="owner-stat-grid">
        <div class="owner-stat-card" style="border-left: 4px solid #10b981;">
          <div class="owner-stat-title">💰 Jami daromad (USD)</div>
          <div class="owner-stat-value" style="color: #10b981;">$${(sum.total_income_usd || 0).toLocaleString()}</div>
          <div class="owner-stat-sub">Tasdiqlangan bronlar bo'yicha</div>
        </div>

        <div class="owner-stat-card" style="border-left: 4px solid #3b82f6;">
          <div class="owner-stat-title">💳 Jami daromad (UZS)</div>
          <div class="owner-stat-value" style="color: #3b82f6;">${(sum.total_income_uzs || 0).toLocaleString()} so'm</div>
          <div class="owner-stat-sub">So'mda kelishilgan bronlar</div>
        </div>

        <div class="owner-stat-card" style="border-left: 4px solid #f59e0b;">
          <div class="owner-stat-title">📅 Band kunlar soni</div>
          <div class="owner-stat-value" style="color: #f59e0b;">${sum.total_booked_days || 0} kun</div>
          <div class="owner-stat-sub">Bandlik darajasi: ${sum.occupancy_rate || 0}%</div>
        </div>

        <div class="owner-stat-card" style="border-left: 4px solid #8b5cf6;">
          <div class="owner-stat-title">📋 Tasdiqlangan bronlar</div>
          <div class="owner-stat-value" style="color: #8b5cf6;">${sum.confirmed_bookings || 0} ta</div>
          <div class="owner-stat-sub">Jami so'rovlar: ${sum.total_bookings || 0} ta</div>
        </div>
      </div>

      <!-- Sources Breakdown & Monthly Trend -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Sources -->
        <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem;">
          <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">📱 Bronlar va Daromad Manbalari</h4>
          <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">Telegram yoki ilova orqali qancha daromad topganingiz:</p>
          <div>
            ${sources.map(s => {
              const incomeText = s.income_usd > 0 
                ? `$${s.income_usd.toLocaleString()}` 
                : (s.income_uzs > 0 ? `${s.income_uzs.toLocaleString()} so'm` : '0');
              return `
                <div class="source-item">
                  <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.5rem;">${s.icon}</span>
                    <div>
                      <div style="font-weight: 700; font-size: 0.9rem; color: var(--dark);">${escapeHtml(s.label)}</div>
                      <div style="font-size: 0.75rem; color: var(--text-muted);">${s.count} ta bron</div>
                    </div>
                  </div>
                  <div style="text-align: right; font-weight: 800; color: var(--primary); font-size: 0.95rem;">
                    ${incomeText}
                  </div>
                </div>
              `;
            }).join('')}
          </div>
        </div>

        <!-- Monthly Trend -->
        <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem;">
          <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">📈 Oylik Daromad Dinamikasi</h4>
          <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">So'nggi oylardagi tushumlar o'sishi:</p>
          <div>
            ${monthly.map(m => {
              const incomeText = m.income_usd > 0 
                ? `$${m.income_usd.toLocaleString()}` 
                : (m.income_uzs > 0 ? `${m.income_uzs.toLocaleString()} so'm` : '0');
              return `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0; border-bottom: 1px dashed var(--border);">
                  <span style="font-weight: 700; font-size: 0.85rem; color: var(--dark);">${escapeHtml(m.month_name)}</span>
                  <div style="display: flex; gap: 1rem; align-items: center;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">${m.bookings_count} bron</span>
                    <strong style="color: var(--primary); font-size: 0.9rem;">${incomeText}</strong>
                  </div>
                </div>
              `;
            }).join('')}
          </div>
        </div>
      </div>

      <!-- Dachas Breakdown -->
      ${dachasBreakdown.length > 0 ? `
        <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem;">
          <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--dark); margin-bottom: 0.75rem;">🏡 Dachalar Kesimida Daromad</h4>
          <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            ${dachasBreakdown.map(d => {
              const incomeText = d.income_usd > 0 
                ? `$${d.income_usd.toLocaleString()}` 
                : (d.income_uzs > 0 ? `${d.income_uzs.toLocaleString()} so'm` : '0');
              return `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                  <div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: var(--dark);">${escapeHtml(d.name)}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">${d.bookings_count} ta muvaffaqiyatli bron</div>
                  </div>
                  <strong style="font-size: 1.05rem; color: var(--primary);">${incomeText}</strong>
                </div>
              `;
            }).join('')}
          </div>
        </div>
      ` : ''}
    </div>
  `;
}

function handleManualSourceChange() {
  const source = document.querySelector('input[name="manualSource"]:checked')?.value || 'telegram';
  const priceInput = document.getElementById('blockPrice');
  const reasonInput = document.getElementById('blockReason');

  if (source === 'manual') {
    if (priceInput) priceInput.placeholder = "0 (Yopish uchun narx shart emas)";
    if (reasonInput) reasonInput.placeholder = "Masalan: Ta'mir yoki o'zimiz dam olamiz";
  } else if (source === 'telegram') {
    if (priceInput) priceInput.placeholder = "Kelishilgan ijara narxi (masalan 1500000)";
    if (reasonInput) reasonInput.placeholder = "Telegram orqali kelishildi";
  } else if (source === 'phone') {
    if (priceInput) priceInput.placeholder = "Kelishilgan ijara narxi (masalan 1500000)";
    if (reasonInput) reasonInput.placeholder = "Telefon orqali bron qilindi";
  }
}

async function loadOwnerDachas() {
  const container = document.getElementById('ownerDachasList');
  if (!container) return;

  container.innerHTML = `
    <div style="text-align: center; padding: 3rem;">
      <div style="display:inline-block; width: 35px; height: 35px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top: 0.75rem; color: var(--text-muted); font-size: 0.9rem;">Dachalaringiz yuklanmoqda...</p>
    </div>
  `;

  try {
    const res = await fetch(`${API_BASE}/owner/dachas`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    if (res.ok) {
      const data = await res.json();
      state.ownerDachas = data.data || [];
      const countEl = document.getElementById('ownerDachasCount');
      if (countEl) countEl.textContent = state.ownerDachas.length;
      renderOwnerDachasList(state.ownerDachas);
      populateBlockDatesDachaSelect(state.ownerDachas);
    } else {
      container.innerHTML = `<p style="color: red; text-align: center; padding: 2rem;">Dachalarni yuklashda xatolik yuz berdi.</p>`;
    }
  } catch (err) {
    console.error('loadOwnerDachas error:', err);
    container.innerHTML = `<p style="color: red; text-align: center; padding: 2rem;">Server bilan bog'lanishda xatolik.</p>`;
  }
}

function populateBlockDatesDachaSelect(dachas) {
  const select = document.getElementById('blockDatesDachaSelect');
  if (!select) return;

  if (!dachas || dachas.length === 0) {
    select.innerHTML = '<option value="">Avval dacha e\'lonini joylang</option>';
    return;
  }

  select.innerHTML = dachas.map(d => `<option value="${d.id}">${escapeHtml(d.name)} (${d.district || d.region})</option>`).join('');
}

function renderOwnerDachasList(dachas) {
  const container = document.getElementById('ownerDachasList');
  if (!container) return;

  if (!dachas || dachas.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 4rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">🏡</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hozircha e'lonlaringiz yo'q</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">Yangi dacha e'lonini joylashtiring va mijozlar qabul qilishni boshlang.</p>
        <button class="btn btn-primary" onclick="closeModal('ownerCabinetModal'); openOwnerModal();">
          ➕ Yangi dacha e'lonini joylash
        </button>
      </div>
    `;
    return;
  }

  container.innerHTML = `
    <div class="owner-dacha-grid">
      ${dachas.map(dacha => {
        const firstImg = dacha.media && dacha.media.length > 0 
          ? dacha.media[0].url 
          : '/storage/dachas/images/dacha_1_1.jpg';
        const currencySymbol = dacha.currency === 'UZS' ? 'so\'m' : '$';
        const weekdayPrice = parseFloat(dacha.weekday_price || dacha.default_price || 0);
        const weekendPrice = parseFloat(dacha.weekend_price || dacha.weekday_price || dacha.default_price || 0);
        const statusBadgeClass = dacha.status === 'active' ? 'badge-active' : (dacha.status === 'pending' ? 'badge-pending' : 'badge-inactive');
        const statusText = dacha.status === 'active' ? 'Faol' : (dacha.status === 'pending' ? 'Kutilmoqda' : 'Nofaol');

        return `
          <div class="owner-dacha-card">
            <div class="owner-dacha-img-wrap">
              <img src="${firstImg}" alt="${escapeHtml(dacha.name)}" />
              <div class="owner-dacha-status-badge ${statusBadgeClass}">
                ● ${statusText}
              </div>
            </div>
            <div class="owner-dacha-info">
              <h3 class="owner-dacha-title">${escapeHtml(dacha.name)}</h3>
              <div class="owner-dacha-loc">
                <span>📍</span> ${escapeHtml(dacha.region || '')}, ${escapeHtml(dacha.district || '')}
              </div>
              <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                👥 ${dacha.capacity || 1} kishilik • 🛏️ ${dacha.rooms_count || 1} xona
              </div>
              <div class="owner-dacha-prices">
                <div>
                  <span style="font-size: 0.75rem; color: var(--text-muted);">Ish kunlari:</span>
                  <div style="font-weight: 700; color: var(--dark);">${weekdayPrice.toLocaleString()} ${currencySymbol}</div>
                </div>
                <div style="text-align: right;">
                  <span style="font-size: 0.75rem; color: var(--text-muted);">Dam olish:</span>
                  <div style="font-weight: 700; color: var(--accent);">${weekendPrice.toLocaleString()} ${currencySymbol}</div>
                </div>
              </div>
              <div class="owner-dacha-actions">
                <button class="btn btn-outline" onclick="closeModal('ownerCabinetModal'); openDachaDetail(${dacha.id});" title="Mijozlar ko'radigan sahifani ochish">
                  👁️ Ko'rish
                </button>
                <button class="btn btn-outline" style="color: #2563eb; border-color: #bfdbfe;" onclick="openEditDachaModal(${dacha.id})">
                  ✏️ Tahrirlash
                </button>
                <button class="btn btn-outline" style="color: #d97706; border-color: #fde68a;" onclick="openBlockDatesForDacha(${dacha.id})">
                  ➕ Tashqi bron
                </button>
                <button class="btn btn-outline" style="color: #ef4444; border-color: #fecaca;" onclick="deleteOwnerDacha(${dacha.id})">
                  🗑️
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('')}
    </div>
  `;
}

async function loadOwnerBookings() {
  const container = document.getElementById('ownerBookingsList');
  if (!container) return;

  container.innerHTML = `
    <div style="text-align: center; padding: 3rem;">
      <div style="display:inline-block; width: 35px; height: 35px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top: 0.75rem; color: var(--text-muted); font-size: 0.9rem;">Bron so'rovlari yuklanmoqda...</p>
    </div>
  `;

  try {
    const res = await fetch(`${API_BASE}/owner/bookings`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    if (res.ok) {
      const data = await res.json();
      state.ownerBookings = data.data || [];
      const countEl = document.getElementById('ownerBookingsCount');
      if (countEl) countEl.textContent = state.ownerBookings.length;
      renderOwnerBookingsList(state.ownerBookings);
    } else {
      container.innerHTML = `<p style="color: red; text-align: center; padding: 2rem;">Bronlarni yuklashda xatolik yuz berdi.</p>`;
    }
  } catch (err) {
    console.error('loadOwnerBookings error:', err);
    container.innerHTML = `<p style="color: red; text-align: center; padding: 2rem;">Server bilan bog'lanishda xatolik.</p>`;
  }
}

function renderOwnerBookingsList(bookings) {
  const container = document.getElementById('ownerBookingsList');
  if (!container) return;

  if (!bookings || bookings.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 3.5rem 1rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📋</div>
        <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hozircha bron so'rovlari yo'q</h4>
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

    let sourceBadge = `<span style="display:inline-block; font-size:0.75rem; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:6px; font-weight:700;">🌟 Oromgo</span>`;
    if (b.source === 'telegram') {
      sourceBadge = `<span style="display:inline-block; font-size:0.75rem; background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:6px; font-weight:700;">📱 Telegram</span>`;
    } else if (b.source === 'phone') {
      sourceBadge = `<span style="display:inline-block; font-size:0.75rem; background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:6px; font-weight:700;">📞 Telefon</span>`;
    } else if (b.source === 'manual') {
      sourceBadge = `<span style="display:inline-block; font-size:0.75rem; background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:6px; font-weight:700;">🚫 Yopilgan</span>`;
    }

    let statusBadge = `<span class="notif-type-badge booking_created">Kutilmoqda ⏳</span>`;
    if (isConfirmed) statusBadge = `<span class="notif-type-badge booking_confirmed">Tasdiqlangan ✅</span>`;
    if (isCancelled) statusBadge = `<span class="notif-type-badge booking_cancelled">Bekor qilingan ❌</span>`;

    return `
      <div class="owner-booking-card">
        <div class="owner-booking-header">
          <div>
            <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
              🏡 ${escapeHtml(dachaName)} ${sourceBadge}
            </div>
            <div style="font-size: 0.825rem; color: var(--text-muted); margin-top: 0.15rem;">Bron raqami: #${b.id} • ${formatTimeAgo(b.created_at)}</div>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            ${statusBadge}
            ${(b.source !== 'app' || isCancelled) ? `
              <button class="btn btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; color: #ef4444; border-color: #fecaca;" onclick="deleteOwnerBooking(${b.id})" title="O'chirish">🗑️</button>
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
            <strong style="color: var(--primary);">${totalPrice} ${currency}</strong>
          </div>
          ${b.notes ? `
            <div style="grid-column: 1/-1;">
              <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">💬 Izoh:</span>
              <span style="color: var(--dark); font-size: 0.85rem;">${escapeHtml(b.notes)}</span>
            </div>
          ` : ''}
        </div>

        ${isPending ? `
          <div style="display: flex; gap: 0.75rem; margin-top: 0.25rem;">
            <button class="btn-confirm-sm" onclick="handleOwnerBookingDecisionInCabinet(${b.id}, 'confirm')">
              <span>✅</span> Tasdiqlash
            </button>
            <button class="btn-reject-sm" onclick="handleOwnerBookingDecisionInCabinet(${b.id}, 'reject')">
              <span>❌</span> Rad etish
            </button>
          </div>
        ` : ''}
      </div>
    `;
  }).join('');
}

async function deleteOwnerBooking(bookingId) {
  if (!confirm('Ushbu bron yoki yopilgan sanalarni o\'chirmoqchimisiz?')) {
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/owner/bookings/${bookingId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    const data = await res.json();
    if (res.ok) {
      showToast('Bron muvaffaqiyatli o\'chirildi.', 'success');
      await Promise.all([loadOwnerBookings(), loadOwnerReports()]);
    } else {
      showToast(data.message || 'O\'chirishda xatolik yuz berdi', 'error');
    }
  } catch (err) {
    console.error('deleteOwnerBooking error:', err);
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

async function handleOwnerBookingDecisionInCabinet(bookingId, action) {
  try {
    const res = await fetch(`${API_BASE}/owner/bookings/${bookingId}/${action}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    const data = await res.json();
    if (res.ok) {
      showToast(action === 'confirm' ? '🎉 Bron tasdiqlandi!' : 'Bron rad etildi.', 'success');
      await Promise.all([loadOwnerBookings(), loadOwnerReports()]);
      loadNotifications(false);
    } else {
      showToast(data.message || 'Xatolik yuz berdi', 'error');
    }
  } catch (err) {
    console.error('handleOwnerBookingDecisionInCabinet error:', err);
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

async function deleteOwnerDacha(dachaId) {
  if (!confirm('Haqiqatan ham ushbu dacha e\'lonini o\'chirmoqchimisiz?')) {
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/owner/dachas/${dachaId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    const data = await res.json();
    if (res.ok) {
      showToast('Dacha muvaffaqiyatli o\'chirildi.', 'success');
      await loadOwnerDachas();
      loadDachas();
      loadOwnerReports();
    } else {
      showToast(data.message || 'O\'chirishda xatolik yuz berdi', 'error');
    }
  } catch (err) {
    console.error('deleteOwnerDacha error:', err);
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

function openBlockDatesForDacha(dachaId) {
  openOwnerCabinetModal('blockDates');
  setTimeout(() => {
    const select = document.getElementById('blockDatesDachaSelect');
    if (select && dachaId) {
      select.value = dachaId;
    }
  }, 200);
}

async function handleBlockDatesSubmit(e) {
  e.preventDefault();

  const dachaId = document.getElementById('blockDatesDachaSelect')?.value;
  const startDate = document.getElementById('blockStartDate')?.value;
  const endDate = document.getElementById('blockEndDate')?.value;
  const source = document.querySelector('input[name="manualSource"]:checked')?.value || 'telegram';
  const price = parseFloat(document.getElementById('blockPrice')?.value || 0);
  const currency = document.getElementById('blockCurrency')?.value || 'USD';
  const customerName = document.getElementById('blockCustomerName')?.value;
  const customerPhone = document.getElementById('blockCustomerPhone')?.value;
  const reason = document.getElementById('blockReason')?.value;

  if (!dachaId || !startDate || !endDate) {
    showToast('Barcha majburiy maydonlarni to\'ldiring', 'error');
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
      notes: reason || null
    };

    if (source === 'manual' && price === 0) {
      endpoint = `${API_BASE}/owner/dachas/${dachaId}/block-dates`;
      bodyData = {
        start_date: startDate,
        end_date: endDate,
        reason: reason,
        total_price: 0,
        currency: currency,
        source: 'manual',
        customer_name: customerName,
        customer_phone: customerPhone
      };
    }

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(bodyData)
    });

    const data = await res.json();
    if (res.ok) {
      showToast('🎉 Tashqi bron / Sanalar saqlandi va hisobotga kiritildi!', 'success');
      document.getElementById('ownerBlockDatesForm')?.reset();
      await Promise.all([loadOwnerBookings(), loadOwnerReports()]);
      switchCabinetTab('reports');
    } else {
      showToast(data.message || 'Sanalarni saqlashda xatolik', 'error');
    }
  } catch (err) {
    console.error('handleBlockDatesSubmit error:', err);
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

async function openEditDachaModal(dachaId) {
  const dacha = state.ownerDachas.find(d => d.id === dachaId);
  if (!dacha) {
    showToast('Dacha ma\'lumotlari topilmadi', 'error');
    return;
  }

  document.getElementById('editDachaId').value = dacha.id;
  document.getElementById('editName').value = dacha.name || '';
  document.getElementById('editDescription').value = dacha.description || '';
  document.getElementById('editWeekdayPrice').value = dacha.weekday_price || dacha.default_price || '';
  document.getElementById('editWeekendPrice').value = dacha.weekend_price || '';
  document.getElementById('editCurrency').value = dacha.currency || 'USD';
  document.getElementById('editCapacity').value = dacha.capacity || 10;
  document.getElementById('editRoomsCount').value = dacha.rooms_count || 4;
  document.getElementById('editMahalla').value = dacha.mahalla || '';
  document.getElementById('editAddress').value = dacha.address || '';

  // Populate regions
  populateRegionSelects();
  const editRegSelect = document.getElementById('editRegion');
  if (editRegSelect && dacha.region) {
    editRegSelect.value = dacha.region;
    populateDistrictsForSelect('editDistrict', dacha.region, 'Tumanni tanlang');
    const editDistSelect = document.getElementById('editDistrict');
    if (editDistSelect && dacha.district) {
      editDistSelect.value = dacha.district;
    }
  }

  // Render amenities checkboxes
  const container = document.getElementById('editAmenitiesCheckboxes');
  if (container) {
    const dachaAmenityIds = (dacha.amenities || []).map(a => a.id);
    container.innerHTML = state.amenities.map(a => `
      <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; background: var(--bg-page); padding: 0.4rem 0.65rem; border-radius: var(--radius-sm); border: 1px solid var(--border); cursor: pointer;">
        <input type="checkbox" name="amenities[]" value="${a.id}" ${dachaAmenityIds.includes(a.id) ? 'checked' : ''} />
        <span>${a.icon || '✨'}</span> ${escapeHtml(a.name)}
      </label>
    `).join('');
  }

  openModal('editDachaModal');
}

async function handleUpdateDacha(e) {
  e.preventDefault();

  const dachaId = document.getElementById('editDachaId').value;
  if (!dachaId) return;

  const form = document.getElementById('editDachaForm');
  const formData = new FormData(form);

  formData.set('name', document.getElementById('editName').value);
  formData.set('description', document.getElementById('editDescription').value);
  formData.set('region', document.getElementById('editRegion').value);
  formData.set('district', document.getElementById('editDistrict').value);
  formData.set('mahalla', document.getElementById('editMahalla').value);
  formData.set('address', document.getElementById('editAddress').value);
  formData.set('weekday_price', document.getElementById('editWeekdayPrice').value);
  formData.set('weekend_price', document.getElementById('editWeekendPrice').value);
  formData.set('currency', document.getElementById('editCurrency').value);
  formData.set('capacity', document.getElementById('editCapacity').value);
  formData.set('rooms_count', document.getElementById('editRoomsCount').value);
  formData.append('_method', 'PUT');

  const btn = form.querySelector('button[type="submit"]');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.textContent = 'Saqlanmoqda...';

  try {
    const res = await fetch(`${API_BASE}/owner/dachas/${dachaId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    const result = await res.json();
    if (res.ok) {
      showToast('🎉 Dacha ma\'lumotlari muvaffaqiyatli yangilandi!', 'success');
      closeModal('editDachaModal');
      await loadOwnerDachas();
      loadDachas();
    } else {
      let errMsg = result.message || 'Xatolik yuz berdi';
      if (result.errors) {
        errMsg = Object.values(result.errors).flat().join('<br>');
      }
      showToast(errMsg, 'error');
    }
  } catch (err) {
    console.error('handleUpdateDacha error:', err);
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalText;
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

async function loginAsDemo(role = 'owner') {
  try {
    const res = await fetch(`${API_BASE}/demo-login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ role })
    });

    if (res.ok) {
      const data = await res.json();
      state.token = data.token;
      state.user = data.user;
      localStorage.setItem('oromgo_token', state.token);
      localStorage.setItem('oromgo_user', JSON.stringify(state.user));
      updateAuthUI();
      closeModal('authModal');
      const roleTitle = role === 'admin' ? '🛡️ Admin Moderator' : (role === 'owner' ? '🏡 Dacha egasi' : '👤 Mijoz');
      showToast(`${roleTitle} sifatida tizimga kirdingiz!`, 'success');
      await loadFavorites();
      renderDachas(state.dachas);
      loadNotifications(true);

      // Agar Admin sifatida kirgan bo'lsa, to'g'ridan-to'g'ri /admin sahifasiga o'tadi!
      if (role === 'admin' || (state.user && (state.user.role === 'admin' || state.user.role === 'super_admin'))) {
        window.location.href = '/admin';
        return;
      }

      // Agar avval biror dachani batafsil ko'rmoqchi bo'lgan bo'lsa, o'shani darhol ochib beramiz!
      if (state.pendingDachaId) {
        const targetId = state.pendingDachaId;
        state.pendingDachaId = null;
        openDachaDetail(targetId);
      }

      // Agar e'lon berish tugmasini bosgan bo'lsa, e'lon berish modalini ochamiz
      if (state.pendingAction === 'open_owner_modal') {
        state.pendingAction = null;
        if (state.user && (state.user.role === 'owner' || state.user.role === 'admin' || state.user.role === 'super_admin')) {
          openOwnerModal();
        }
      }

      // Agar kabinetni ochmoqchi bo'lgan bo'lsa
      if (state.pendingAction === 'open_owner_cabinet') {
        state.pendingAction = null;
        if (state.user && (state.user.role === 'owner' || state.user.role === 'admin' || state.user.role === 'super_admin')) {
          openOwnerCabinetModal('dachas');
        }
      }
    } else {
      showToast('Kirishda xatolik yuz berdi', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

function loginAsDemoOwner() {
  loginAsDemo('owner');
}

function updateAuthUI() {
  const userBox = document.getElementById('navUserBox');
  const myDachasPillBtn = document.getElementById('myDachasPillBtn');
  const adminPanelNavBtn = document.getElementById('adminPanelNavBtn');
  if (!userBox) return;

  const isOwner = state.user && (state.user.role === 'owner');
  const isAdmin = state.user && (state.user.role === 'admin' || state.user.role === 'super_admin');

  if (myDachasPillBtn) {
    myDachasPillBtn.style.display = (state.token && isOwner) ? 'inline-block' : 'none';
  }

  if (adminPanelNavBtn) {
    adminPanelNavBtn.style.display = (state.token && isAdmin) ? 'inline-flex' : 'none';
    if (isAdmin && state.token) {
      loadAdminStats();
    }
  }

  if (state.user && state.token) {
    userBox.innerHTML = `
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        ${isAdmin ? `
          <button class="btn btn-outline" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; border-color: #7c3aed; color: #7c3aed; font-weight: 700; background: #f5f3ff;" onclick="openAdminModal()">
            🛡️ Admin Kabinet
          </button>
        ` : (isOwner ? `
          <button class="btn btn-outline" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; border-color: var(--primary); color: var(--primary); font-weight: 700;" onclick="openOwnerCabinetModal('dachas')">
            🗂️ Kabinet
          </button>
        ` : '')}
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
  state.favoriteIds = [];
  localStorage.removeItem('oromgo_token');
  localStorage.removeItem('oromgo_user');
  updateAuthUI();
  showToast('Tizimdan chiqdingiz.', 'success');
  loadDachas();
}

function resetSearch() {
  const form = document.getElementById('searchForm');
  if (form) form.reset();
  state.activeFilter = 'all';
  document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
  const allBtn = document.querySelector('.pill-btn[onclick*="all"]');
  if (allBtn) allBtn.classList.add('active');
  loadDachas();
}

function filterByCategory(btn, category) {
  document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  state.activeFilter = category;

  if (category === 'all') {
    loadDachas();
  } else if (category === 'favorites') {
    showFavoritesOnly(btn);
  } else {
    loadDachas({ category });
  }
}

// ==========================================
// NOTIFICATIONS CENTER & TELEGRAM BOT INTEGRATION
// ==========================================

async function loadNotifications(render = true) {
  if (!state.token) {
    state.notifications = [];
    state.unreadNotificationsCount = 0;
    updateNotificationBadgeUI();
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/notifications`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    if (res.ok) {
      const data = await res.json();
      state.notifications = data.notifications?.data || [];
      state.unreadNotificationsCount = data.unread_count || 0;
      state.hasTelegramLinked = data.has_telegram_linked || false;

      updateNotificationBadgeUI();
      if (render) {
        renderNotificationsList();
      }
    }
  } catch (err) {
    console.error('Bildirishnomalarni yuklashda xatolik:', err);
  }
}

function updateNotificationBadgeUI() {
  const badge = document.getElementById('unreadBadgeCount');
  if (!badge) return;

  if (state.unreadNotificationsCount > 0) {
    badge.textContent = state.unreadNotificationsCount > 99 ? '99+' : state.unreadNotificationsCount;
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }

  // Update counters in tabs
  const totalCountEl = document.getElementById('notifTotalCount');
  const unreadCountEl = document.getElementById('notifUnreadCount');
  if (totalCountEl) totalCountEl.textContent = state.notifications.length;
  if (unreadCountEl) unreadCountEl.textContent = state.unreadNotificationsCount;

  // Update Telegram Banner
  const tgStatus = document.getElementById('telegramStatusText');
  const tgBtn = document.querySelector('.btn-telegram-sm');
  if (tgStatus) {
    if (state.hasTelegramLinked) {
      tgStatus.innerHTML = '✅ Telegram profilingiz muvaffaqiyatli ulangan. Barcha xabarnomalar botga kelmoqda.';
      if (tgBtn) tgBtn.innerHTML = '<span>💬 Botni ochish</span>';
    } else {
      tgStatus.innerHTML = 'Yangi bron so\'rovlarini Telegramda ko\'rish va <b>[Tasdiqlash]</b> tugmalarini bosish uchun botni ulang.';
      if (tgBtn) tgBtn.innerHTML = '<span>🚀 Botga ulanish</span>';
    }
  }
}

function toggleNotificationsModal() {
  if (!state.token) {
    openAuthModal('Bildirishnomalarni ko\'rish uchun tizimga kiring');
    return;
  }
  openModal('notificationModal');
  loadNotifications(true);
}

function filterNotifications(type, btn) {
  state.notifFilter = type;
  document.querySelectorAll('.notif-tab-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  renderNotificationsList();
}

function renderNotificationsList() {
  const list = document.getElementById('notificationList');
  if (!list) return;

  let items = [...state.notifications];

  if (state.notifFilter === 'unread') {
    items = items.filter(n => !n.is_read);
  } else if (state.notifFilter === 'bookings') {
    items = items.filter(n => ['booking_created', 'booking_confirmed', 'booking_cancelled'].includes(n.type));
  }

  if (items.length === 0) {
    list.innerHTML = `
      <div style="text-align: center; padding: 3.5rem 1rem; color: var(--text-muted);">
        <div style="font-size: 3rem; margin-bottom: 0.75rem;">📭</div>
        <h4 style="font-size: 1.1rem; color: var(--dark); margin-bottom: 0.25rem;">Bildirishnomalar yo'q</h4>
        <p style="font-size: 0.875rem;">Hozircha hech qanday yangi xabarnoma mavjud emas.</p>
      </div>
    `;
    return;
  }

  const isOwnerOrAdmin = state.user && (state.user.role === 'owner' || state.user.role === 'admin' || state.user.role === 'super_admin');

  list.innerHTML = items.map(n => {
    const isUnread = !n.is_read;
    const data = n.data || {};
    const bookingId = n.booking_id || data.booking_id;
    const dachaId = data.dacha_id || n.booking?.dacha_id || n.booking?.dacha?.id;
    const timeAgo = formatTimeAgo(n.created_at);

    let typeBadgeLabel = 'Xabar';
    let icon = 'ℹ️';

    if (n.type === 'booking_created') {
      typeBadgeLabel = 'Yangi Bron';
      icon = '🔔';
    } else if (n.type === 'booking_confirmed') {
      typeBadgeLabel = 'Tasdiqlangan';
      icon = '✅';
    } else if (n.type === 'booking_cancelled') {
      typeBadgeLabel = 'Bekor qilingan';
      icon = '❌';
    } else if (n.type === 'booking_reminder') {
      typeBadgeLabel = 'Eslatma';
      icon = '⏰';
    }

    // Owner action buttons if booking is pending
    const showOwnerActions = isOwnerOrAdmin && n.type === 'booking_created' && bookingId && data.status === 'pending';

    return `
      <div class="notif-card ${isUnread ? 'unread' : ''}" onclick="handleNotificationClick(${n.id}, ${dachaId || 'null'})" title="${dachaId ? 'Dachani batafsil ko\'rish uchun bosing' : ''}">
        <div class="notif-card-header">
          <div class="notif-title-row">
            <span>${icon}</span>
            <strong style="font-size: 0.95rem; color: var(--dark);">${escapeHtml(n.title)}</strong>
            <span class="notif-type-badge ${n.type}">${typeBadgeLabel}</span>
          </div>
          <span class="notif-time">${timeAgo}</span>
        </div>

        <p style="font-size: 0.875rem; color: var(--dark-light); margin: 0; line-height: 1.5;">${escapeHtml(n.message)}</p>

        ${data.dacha_name || data.start_date ? `
          <div class="notif-details-grid">
            ${data.dacha_name ? `
              <div class="notif-detail-item" style="grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; background: #eff6ff; padding: 0.4rem 0.6rem; border-radius: 6px; border: 1px solid #bfdbfe;">
                <div>
                  <span class="notif-detail-label" style="color: #1e40af;">🏡 Dacha:</span>
                  <span class="notif-detail-val" style="color: #1e3a8a; font-weight: 700;">${escapeHtml(data.dacha_name)}</span>
                </div>
                ${dachaId ? `<span style="font-size: 0.75rem; font-weight: 700; color: #2563eb;">Batafsil ko'rish ➔</span>` : ''}
              </div>
            ` : ''}
            ${data.guest_name ? `
              <div class="notif-detail-item">
                <span class="notif-detail-label">👤 Mijoz:</span>
                <span class="notif-detail-val">${escapeHtml(data.guest_name)}</span>
              </div>
            ` : ''}
            ${data.guest_phone ? `
              <div class="notif-detail-item">
                <span class="notif-detail-label">📞 Tel:</span>
                <span class="notif-detail-val">${escapeHtml(data.guest_phone)}</span>
              </div>
            ` : ''}
            ${data.start_date ? `
              <div class="notif-detail-item">
                <span class="notif-detail-label">📅 Sanalar:</span>
                <span class="notif-detail-val">${data.start_date} — ${data.end_date || ''}</span>
              </div>
            ` : ''}
            ${data.total_price ? `
              <div class="notif-detail-item">
                <span class="notif-detail-label">💰 Summa:</span>
                <span class="notif-detail-val" style="color: var(--primary); font-weight: 700;">${parseFloat(data.total_price).toLocaleString()} ${data.currency || 'USD'}</span>
              </div>
            ` : ''}
            ${data.notes ? `
              <div class="notif-detail-item" style="grid-column: 1 / -1;">
                <span class="notif-detail-label">💬 Izoh:</span>
                <span class="notif-detail-val" style="font-weight: 500; color: var(--text-muted);">${escapeHtml(data.notes)}</span>
              </div>
            ` : ''}
          </div>
        ` : ''}

        ${showOwnerActions ? `
          <div class="notif-actions" onclick="event.stopPropagation()">
            <button class="btn-confirm-sm" onclick="handleOwnerNotificationDecision(${bookingId}, 'confirm', ${n.id})">
              <span>✅</span> Tasdiqlash
            </button>
            <button class="btn-reject-sm" onclick="handleOwnerNotificationDecision(${bookingId}, 'reject', ${n.id})">
              <span>❌</span> Rad etish
            </button>
            ${dachaId ? `
              <button class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; margin-left: auto;" onclick="closeModal('notificationModal'); openDachaDetail(${dachaId});">
                🏡 Dachani ko'rish
              </button>
            ` : ''}
          </div>
        ` : (dachaId ? `
          <div style="display: flex; justify-content: flex-end; margin-top: 0.25rem;">
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 0.25rem;">
              Dacha sahifasini ochish ➔
            </span>
          </div>
        ` : '')}
      </div>
    `;
  }).join('');
}

async function handleNotificationClick(notifId, dachaId) {
  // O'qilgan deb belgilash
  markNotificationAsRead(notifId);

  // Agar bildirishnoma biror dachaga biriktirilgan bo'lsa, bildirishnomalar oynasini yopib, dachaning batafsil ma'lumotlarini ochamiz
  if (dachaId) {
    closeModal('notificationModal');
    openDachaDetail(dachaId);
  }
}

async function markNotificationAsRead(id) {
  const notif = state.notifications.find(n => n.id === id);
  if (notif && notif.is_read) return;

  if (notif) notif.is_read = true;
  state.unreadNotificationsCount = Math.max(0, state.unreadNotificationsCount - 1);
  updateNotificationBadgeUI();

  try {
    await fetch(`${API_BASE}/notifications/${id}/read`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });
  } catch (err) {}
}

async function markAllNotificationsAsRead() {
  state.notifications.forEach(n => n.is_read = true);
  state.unreadNotificationsCount = 0;
  updateNotificationBadgeUI();
  renderNotificationsList();

  try {
    await fetch(`${API_BASE}/notifications/read-all`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });
    showToast('Barcha bildirishnomalar o\'qilgan deb belgilandi.', 'success');
  } catch (err) {}
}

async function handleOwnerNotificationDecision(bookingId, action, notifId) {
  try {
    const endpoint = action === 'confirm'
      ? `${API_BASE}/owner/bookings/${bookingId}/confirm`
      : `${API_BASE}/owner/bookings/${bookingId}/reject`;

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    const data = await res.json();

    if (!res.ok) {
      showToast(data.message || 'Xatolik yuz berdi', 'error');
      return;
    }

    showToast(data.message || (action === 'confirm' ? 'Bron tasdiqlandi!' : 'Bron rad etildi.'), 'success');
    
    // Mark this notification as read and update state
    await markNotificationAsRead(notifId);
    await loadNotifications(true);
  } catch (err) {
    console.error(err);
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

async function openTelegramBotLink() {
  if (!state.token) {
    openAuthModal('Telegram botni ulash uchun avval tizimga kiring');
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/telegram/bot-link`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    if (res.ok) {
      const data = await res.json();
      if (data.link) {
        window.open(data.link, '_blank');
      }
    }
  } catch (err) {
    console.error(err);
  }
}

function formatTimeAgo(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const diffSec = Math.floor((now - date) / 1000);

  if (diffSec < 60) return 'Hozirgina';
  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `${diffMin} daqiqa oldin`;
  const diffHour = Math.floor(diffMin / 60);
  if (diffHour < 24) return `${diffHour} soat oldin`;
  const diffDay = Math.floor(diffHour / 24);
  if (diffDay < 7) return `${diffDay} kun oldin`;

  return date.toISOString().split('T')[0];
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

const style = document.createElement('style');
style.textContent = `@keyframes spin { to { transform: rotate(360deg); } }`;
document.head.appendChild(style);


// ==========================================
// TOAST NOTIFICATION SYSTEM
// ==========================================
function showToast(message, type = 'success') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.style.position = 'fixed';
    container.style.bottom = '20px';
    container.style.right = '20px';
    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.gap = '10px';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.style.background = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#3b82f6');
  toast.style.color = '#fff';
  toast.style.padding = '12px 20px';
  toast.style.borderRadius = '8px';
  toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
  toast.style.fontFamily = 'var(--font-sans, system-ui)';
  toast.style.fontSize = '0.95rem';
  toast.style.fontWeight = '500';
  toast.style.display = 'flex';
  toast.style.alignItems = 'center';
  toast.style.justifyContent = 'space-between';
  toast.style.transform = 'translateY(100px)';
  toast.style.opacity = '0';
  toast.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
  
  toast.innerHTML = `
    <span>${message}</span>
  `;

  container.appendChild(toast);

  // Animate in
  setTimeout(() => {
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';
  }, 10);

  // Animate out and remove
  setTimeout(() => {
    toast.style.transform = 'translateY(20px)';
    toast.style.opacity = '0';
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, 4000);
}

// ==========================================
// ADMIN MODERATION & STATUS CONTROL SYSTEM
// ==========================================

let adminSearchDebounceTimer = null;

function openAdminModal() {
  window.location.href = '/admin';
}

async function loadAdminStats() {
  if (!state.token) return;
  try {
    const res = await fetch(`${API_BASE}/admin/stats`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      const stats = await res.json();
      state.adminStats = stats;
      
      const totalEl = document.getElementById('adminStatTotal');
      const pendingEl = document.getElementById('adminStatPending');
      const activeEl = document.getElementById('adminStatActive');
      const inactiveEl = document.getElementById('adminStatInactive');

      if (totalEl) totalEl.textContent = stats.total || 0;
      if (pendingEl) pendingEl.textContent = stats.pending || 0;
      if (activeEl) activeEl.textContent = stats.active || 0;
      if (inactiveEl) inactiveEl.textContent = stats.inactive || 0;

      const badge = document.getElementById('adminPendingBadge');
      if (badge) {
        badge.textContent = stats.pending || 0;
        badge.style.display = (stats.pending > 0) ? 'inline-block' : 'none';
      }
    }
  } catch (err) {
    console.error('loadAdminStats error:', err);
  }
}

function filterAdminDachas(btn, status) {
  document.querySelectorAll('#adminFilterTabs .pill-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  state.adminFilter = status;
  loadAdminDachas(status);
}

function debounceAdminSearch() {
  clearTimeout(adminSearchDebounceTimer);
  adminSearchDebounceTimer = setTimeout(() => {
    state.adminSearchQuery = document.getElementById('adminSearchInput')?.value || '';
    loadAdminDachas(state.adminFilter);
  }, 350);
}

async function loadAdminDachas(status = state.adminFilter) {
  const container = document.getElementById('adminDachasList');
  if (!container) return;

  container.innerHTML = `
    <div style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
      <div style="display:inline-block; width: 32px; height: 32px; border: 3px solid var(--border); border-top-color: #7c3aed; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top: 0.75rem; font-size: 0.9rem; font-weight: 600;">E'lonlar yuklanmoqda...</p>
    </div>
  `;

  try {
    const params = new URLSearchParams();
    if (status && status !== 'all') params.append('status', status);
    if (state.adminSearchQuery) params.append('q', state.adminSearchQuery);

    const res = await fetch(`${API_BASE}/admin/dachas?${params.toString()}`, {
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    if (!res.ok) throw new Error('E\'lonlarni yuklashda xatolik');
    const result = await res.json();
    state.adminDachas = result.data || [];

    renderAdminDachas(state.adminDachas);
  } catch (err) {
    container.innerHTML = `
      <div style="text-align: center; padding: 2rem; color: #ef4444; font-weight: 600;">
        E'lonlar ro'yxatini yuklab bo'lmadi.
      </div>
    `;
  }
}

function renderAdminDachas(dachas) {
  const container = document.getElementById('adminDachasList');
  if (!container) return;

  if (dachas.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 3rem; background: white; border-radius: var(--radius-md); border: 1px dashed var(--border);">
        <span style="font-size: 2.5rem;">🏖️</span>
        <h4 style="margin-top: 0.5rem; color: var(--dark); font-weight: 800;">E'lonlar topilmadi</h4>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Tanlangan holat bo'yicha hech qanday dacha mavjud emas.</p>
      </div>
    `;
    return;
  }

  container.innerHTML = dachas.map(d => {
    const mainImg = (d.media && d.media.length > 0)
      ? `/storage/${d.media[0].path}`
      : 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=600&q=80';

    let statusBadge = '';
    if (d.status === 'pending') {
      statusBadge = `<span style="background: #fef08a; color: #854d0e; padding: 3px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 800;">⏳ KUTILMOQDA (MODERATSIYA)</span>`;
    } else if (d.status === 'active') {
      statusBadge = `<span style="background: #bbf7d0; color: #166534; padding: 3px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 800;">🟢 FAOL (SAYTDA KO'RINMOQDA)</span>`;
    } else {
      statusBadge = `<span style="background: #fecdd3; color: #991b1b; padding: 3px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 800;">⏸️ NOFAOL / TO'XTATILGAN</span>`;
    }

    const ownerName = d.owner ? d.owner.name : 'Noma\'lum egasi';
    const ownerPhone = d.owner ? d.owner.phone : '-';

    return `
      <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem 1.25rem; display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); transition: var(--transition-fast);">
        <div style="display: flex; gap: 1rem; align-items: center; flex: 1; min-width: 280px;">
          <img src="${mainImg}" alt="${escapeHtml(d.name)}" style="width: 84px; height: 84px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);" />
          <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
              <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--dark); margin: 0;">${escapeHtml(d.name)}</h4>
              ${statusBadge}
            </div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
              📍 ${escapeHtml(d.region || '')}, ${escapeHtml(d.district || '')} ${d.mahalla ? `(${escapeHtml(d.mahalla)})` : ''}
            </p>
            <p style="font-size: 0.85rem; color: var(--dark); font-weight: 600;">
              👤 Egasi: <span style="font-weight: 700; color: #0284c7;">${escapeHtml(ownerName)}</span> (${escapeHtml(ownerPhone)}) | 💰 ${Number(d.weekday_price).toLocaleString()} ${d.currency || 'USD'}
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
          ${d.status !== 'active' ? `
            <button class="btn" style="background: #16a34a; color: white; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 700;" onclick="updateAdminDachaStatus(${d.id}, 'active')">
              ✅ Faollashtirish
            </button>
          ` : ''}
          ${d.status !== 'inactive' ? `
            <button class="btn" style="background: #eab308; color: #713f12; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 700;" onclick="updateAdminDachaStatus(${d.id}, 'inactive')">
              ⏸️ Nofaol qilish
            </button>
          ` : ''}
          ${d.status !== 'pending' ? `
            <button class="btn btn-outline" style="padding: 0.45rem 0.75rem; font-size: 0.8rem; font-weight: 600;" onclick="updateAdminDachaStatus(${d.id}, 'pending')" title="Qayta kutilmoqda holatiga o'tkazish">
              ⏳ Kutilmoqda
            </button>
          ` : ''}
          <button class="btn btn-outline" style="padding: 0.45rem 0.75rem; font-size: 0.8rem; font-weight: 600;" onclick="openDachaDetail(${d.id})">
            👁️ Ko'rish
          </button>
          <button class="btn" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 0.45rem 0.75rem; font-size: 0.8rem; font-weight: 700;" onclick="deleteAdminDacha(${d.id})" title="O'chirish">
            🗑️
          </button>
        </div>
      </div>
    `;
  }).join('');
}

async function updateAdminDachaStatus(id, newStatus) {
  try {
    const res = await fetch(`${API_BASE}/admin/dachas/${id}/status`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status: newStatus })
    });

    const data = await res.json();

    if (res.ok) {
      const statusNames = { active: 'Faollashtirildi (Saytda ko\'rinadi)', inactive: 'Nofaol holatga o\'tkazildi', pending: 'Moderatsiyaga qaytarildi' };
      showToast(`🎉 Dacha e'loni ${statusNames[newStatus] || newStatus}!`, 'success');
      loadAdminDachas(state.adminFilter);
      loadAdminStats();
      loadDachas();
    } else {
      showToast(data.message || 'Xatolik yuz berdi', 'error');
    }
  } catch (err) {
    showToast('Server bilan bog\'lanishda xatolik yuz berdi', 'error');
  }
}

async function deleteAdminDacha(id) {
  if (!confirm('Haqiqatan ham ushbu dacha e\'lonini butunlay o\'chirmoqchimisiz?')) return;

  try {
    const res = await fetch(`${API_BASE}/admin/dachas/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Accept': 'application/json'
      }
    });

    if (res.ok) {
      showToast('Dacha e\'loni butunlay o\'chirildi', 'success');
      loadAdminDachas(state.adminFilter);
      loadAdminStats();
      loadDachas();
    } else {
      showToast('O\'chirishda xatolik yuz berdi', 'error');
    }
  } catch (err) {
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}
