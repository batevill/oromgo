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
