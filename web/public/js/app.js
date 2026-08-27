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
  selectedRating: 5,
  isMapView: false,
  mapInstance: null,
  markersLayer: null,
  notifications: [],
  unreadNotificationsCount: 0,
  hasTelegramLinked: false,
  notifFilter: 'all',
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
  
  // Polling for live notifications every 20 seconds
  setInterval(() => {
    if (state.token) {
      loadNotifications(false);
    }
  }, 20000);
});

async function initApp() {
  await Promise.all([loadLocations(), loadAmenities(), loadDachas(), loadFavorites(), loadNotifications()]);
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

  // Owner Dacha Create Form Submit
  const createDachaForm = document.getElementById('createDachaForm');
  if (createDachaForm) {
    createDachaForm.addEventListener('submit', handleCreateDacha);
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
      headers: { 'Authorization': `Bearer ${state.token}` }
    });
    if (res.ok) {
      const data = await res.json();
      state.favoriteIds = data.favorite_ids || [];
    }
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
    updateMapMarkers(state.dachas);
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
    const avgRating = dacha.avg_rating ? parseFloat(dacha.avg_rating).toFixed(1) : '5.0';
    const reviewsCount = dacha.reviews_count || 0;
    const isFav = state.favoriteIds.includes(dacha.id);

    return `
      <div class="dacha-card" onclick="openDachaDetail(${dacha.id})">
        <div class="card-img-wrapper">
          <img src="${firstImg}" alt="${dacha.name}" loading="lazy" />
          
          <div class="card-badge-rating">
            ⭐ ${avgRating} <span style="font-weight: 500; font-size: 0.7rem; color: var(--text-muted);">(${reviewsCount})</span>
          </div>

          <button class="card-btn-favorite ${isFav ? 'active' : ''}" onclick="toggleFavorite(event, ${dacha.id})">
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
      : 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=400';

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
// FAVORITES (WISHLIST)
// ==========================================

async function toggleFavorite(e, dachaId) {
  e.stopPropagation();

  if (!state.token) {
    openAuthModal('Dachani sevimlilar ro\'yxatiga qo\'shish uchun avval tizimga kiring.');
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/favorites/${dachaId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${state.token}`,
        'Content-Type': 'application/json'
      }
    });

    const result = await res.json();

    if (!res.ok) {
      showToast(result.message || 'Xatolik yuz berdi', 'error');
      return;
    }

    if (result.is_favorite) {
      if (!state.favoriteIds.includes(dachaId)) state.favoriteIds.push(dachaId);
      showToast('❤️ Sevimlilar ro\'yxatiga saqlandi!', 'success');
    } else {
      state.favoriteIds = state.favoriteIds.filter(id => id !== dachaId);
      showToast('Dacha sevimlilardan o\'chirildi.', 'success');
    }

    const btn = e.currentTarget;
    if (btn) {
      btn.innerHTML = result.is_favorite ? '❤️' : '🤍';
      btn.classList.toggle('active', result.is_favorite);
    }
  } catch (err) {
    showToast('Server bilan bog\'lanishda xatolik', 'error');
  }
}

async function showFavoritesOnly(btn) {
  document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

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
      headers: { 'Authorization': `Bearer ${state.token}` }
    });
    const data = await res.json();
    const favDachas = data.favorites?.data || [];
    state.favoriteIds = data.favorite_ids || [];

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
  if (!state.token) {
    openAuthModal('Dacha haqida batafsil ma\'lumot, narxlar, band kunlar taqvimi va dacha egasining kontaktlarini ko\'rish uchun iltimos, avval ro\'yxatdan o\'ting yoki tizimga kiring.');
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
      'Authorization': `Bearer ${state.token}`
    };

    const [dachaRes, reviewsRes] = await Promise.all([
      fetch(`${API_BASE}/dachas/${id}`, { credentials: 'omit', headers }),
      fetch(`${API_BASE}/dachas/${id}/reviews`)
    ]);
    
    if (dachaRes.status === 401) {
      closeModal('detailModal');
      openAuthModal('Dacha haqida batafsil ma\'lumot va uning to\'liq kontaktlarini ko\'rish uchun iltimos, tizimga kiring.');
      return;
    }

    const dacha = await dachaRes.json();
    const reviewsData = await reviewsRes.json();

    state.currentDacha = dacha;
    state.currentReviews = reviewsData.reviews || [];

    loadDachaCalendar(id);
    renderDachaDetail(dacha, reviewsData);
  } catch (err) {
    content.innerHTML = `<p style="padding: 2rem; color: red;">Xatolik yuz berdi.</p>`;
  }
}

function renderDachaDetail(dacha, reviewsData = { reviews: [], avg_rating: 5.0, total_reviews: 0 }) {
  const content = document.getElementById('detailModalContent');
  const currencySymbol = dacha.currency === 'UZS' ? 'so\'m' : '$';

  const images = dacha.media && dacha.media.length > 0 
    ? dacha.media.map(m => m.url) 
    : ['https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1200&auto=format&fit=crop&q=80'];

  const mainImg = images[0];
  const thumb1 = images[1] || mainImg;
  const thumb2 = images[2] || thumb1;

  const today = new Date().toISOString().split('T')[0];
  const weekdayPrice = parseFloat(dacha.weekday_price || dacha.default_price || 0);
  const weekendPrice = parseFloat(dacha.weekend_price || dacha.weekday_price || dacha.default_price || 0);

  const reviews = reviewsData.reviews || [];
  const avgRating = reviewsData.avg_rating || 5.0;
  const totalReviews = reviewsData.total_reviews || reviews.length;

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
      showToast(`${role === 'owner' ? 'Dacha egasi' : 'Mijoz'} sifatida tizimga kirdingiz!`, 'success');
      loadFavorites();
      loadNotifications(true);
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
  loadDachas();
}

function filterByCategory(btn, category) {
  document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (category === 'all') {
    loadDachas();
  } else if (category === 'favorites') {
    showFavoritesOnly(btn);
  } else {
    loadDachas();
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
      <div class="notif-card ${isUnread ? 'unread' : ''}" onclick="markNotificationAsRead(${n.id})">
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
              <div class="notif-detail-item">
                <span class="notif-detail-label">🏡 Dacha:</span>
                <span class="notif-detail-val">${escapeHtml(data.dacha_name)}</span>
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
                <span class="notif-detail-val" style="color: var(--primary);">${parseFloat(data.total_price).toLocaleString()} ${data.currency || 'USD'}</span>
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
          </div>
        ` : ''}
      </div>
    `;
  }).join('');
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

