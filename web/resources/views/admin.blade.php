<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oromgo - Admin Moderatsiya Paneli</title>
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
    .status-badge-pending {
      background: #fef08a;
      color: #854d0e;
      padding: 4px 10px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
    }
    .status-badge-active {
      background: #bbf7d0;
      color: #166534;
      padding: 4px 10px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
    }
    .status-badge-inactive {
      background: #fecdd3;
      color: #991b1b;
      padding: 4px 10px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
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
        <span class="badge">Moderatsiya</span>
      </a>
      <a href="/" class="btn btn-outline" style="color: #cbd5e1; border-color: #475569; padding: 0.4rem 0.85rem; font-size: 0.85rem;" target="_blank">
        🌐 Asosiy saytga o'tish
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
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">Dachalarni Moderatsiya Qilish</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
          Yangi joylangan dachalarni tekshirish, faollashtirish (saytga chiqarish) yoki to'xtatish.
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
            <th>Status</th>
            <th style="text-align: right;">Harakatlar</th>
          </tr>
        </thead>
        <tbody id="adminTableBody">
          <tr>
            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
              Ma'lumotlar yuklanmoqda...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    const API_BASE = '/api';
    let adminToken = localStorage.getItem('oromgo_token') || '';
    let adminUser = JSON.parse(localStorage.getItem('oromgo_user') || 'null');
    let currentFilter = 'all';
    let searchQuery = '';
    let searchTimer = null;

    document.addEventListener('DOMContentLoaded', () => {
      // Check auth
      if (!adminToken || !adminUser || (adminUser.role !== 'admin' && adminUser.role !== 'super_admin')) {
        // Avtomatik admin demo login orqali kirish imkoni
        loginAdminAuto();
        return;
      }

      document.getElementById('adminUserName').textContent = `👤 ${adminUser.name}`;
      loadAdminData();
    });

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
          <td colspan="6" style="text-align: center; padding: 3rem;">
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
        const dachas = result.data || [];

        renderTable(dachas);
      } catch (err) {
        tbody.innerHTML = `
          <tr>
            <td colspan="6" style="text-align: center; padding: 2.5rem; color: #ef4444; font-weight: 600;">
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
            <td colspan="6" style="text-align: center; padding: 3.5rem; color: var(--text-muted);">
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

        let statusHtml = '';
        if (d.status === 'pending') {
          statusHtml = `<span class="status-badge-pending">⏳ Kutilmoqda</span>`;
        } else if (d.status === 'active') {
          statusHtml = `<span class="status-badge-active">🟢 Faol (Saytda)</span>`;
        } else {
          statusHtml = `<span class="status-badge-inactive">⏸️ Nofaol</span>`;
        }

        const ownerName = d.owner ? d.owner.name : 'Noma\'lum';
        const ownerPhone = d.owner ? d.owner.phone : '-';

        return `
          <tr>
            <td>
              <img src="${img}" alt="${escapeHtml(d.name)}" style="width: 56px; height: 56px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);" />
            </td>
            <td>
              <div style="font-weight: 800; color: #0f172a; margin-bottom: 0.2rem;">${escapeHtml(d.name)}</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);">📍 ${escapeHtml(d.region || '')}, ${escapeHtml(d.district || '')} ${d.mahalla ? `(${escapeHtml(d.mahalla)})` : ''}</div>
            </td>
            <td>
              <div style="font-weight: 700; color: #0284c7;">${escapeHtml(ownerName)}</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);">📞 ${escapeHtml(ownerPhone)}</div>
            </td>
            <td>
              <div style="font-weight: 800; color: #0f172a;">${Number(d.weekday_price).toLocaleString()} ${d.currency || 'USD'}</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);">👥 ${d.capacity || 1} kishi | 🚪 ${d.rooms_count || 1} xona</div>
            </td>
            <td>
              ${statusHtml}
            </td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 0.4rem;">
                ${d.status !== 'active' ? `
                  <button class="btn" style="background: #16a34a; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem; font-weight: 700;" onclick="changeStatus(${d.id}, 'active')">
                    ✅ Faollashtirish
                  </button>
                ` : ''}
                ${d.status !== 'inactive' ? `
                  <button class="btn" style="background: #eab308; color: #713f12; padding: 0.4rem 0.8rem; font-size: 0.8rem; font-weight: 700;" onclick="changeStatus(${d.id}, 'inactive')">
                    ⏸️ Nofaol qilish
                  </button>
                ` : ''}
                <button class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; padding: 0.4rem 0.65rem; font-size: 0.8rem;" onclick="deleteDacha(${d.id})" title="O'chirish">
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

    async function changeStatus(id, newStatus) {
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
          loadAdminData();
        } else {
          alert('Statusni o\'zgartirishda xatolik yuz berdi');
        }
      } catch (e) {
        alert('Server bilan bog\'lanishda xatolik');
      }
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
