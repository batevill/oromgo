<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adminga Murojaat - Oromgo</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💬</text></svg>">
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .chat-page-layout {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: #f8fafc;
    }
    .chat-container {
      max-width: 900px;
      width: 100%;
      margin: 1.5rem auto 2rem;
      padding: 0 1rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .chat-card {
      background: white;
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-md);
      display: flex;
      flex-direction: column;
      flex: 1;
      height: 75vh;
      overflow: hidden;
    }
    .chat-header {
      background: white;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .chat-body {
      flex: 1;
      padding: 1.5rem;
      overflow-y: auto;
      background: #f1f5f9;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .chat-footer {
      padding: 1rem 1.5rem;
      background: white;
      border-top: 1px solid var(--border);
    }
    .chat-bubble {
      max-width: 75%;
      padding: 0.85rem 1.15rem;
      border-radius: 18px;
      font-size: 0.95rem;
      line-height: 1.5;
      position: relative;
      word-break: break-word;
    }
    .chat-bubble-user {
      align-self: flex-end;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      border-bottom-right-radius: 4px;
      box-shadow: 0 4px 12px var(--primary-glow);
    }
    .chat-bubble-admin {
      align-self: flex-start;
      background: white;
      color: var(--dark);
      border: 1px solid var(--border);
      border-bottom-left-radius: 4px;
      box-shadow: var(--shadow-sm);
    }
    .chat-time {
      font-size: 0.7rem;
      margin-top: 0.35rem;
      opacity: 0.75;
      text-align: right;
    }
    .auth-overlay {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      flex: 1;
      padding: 2rem;
      text-align: center;
      background: white;
    }
  </style>
</head>
<body class="chat-page-layout">

  <!-- NAVBAR -->
  <nav class="navbar" style="padding: 0.75rem 1.5rem;">
    <div class="nav-container">
      <a href="/" class="brand-logo">
        <div class="logo-icon">🏡</div>
        <span>Oromgo</span>
      </a>

      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <a href="/" class="btn btn-outline" style="padding: 0.45rem 0.85rem; font-size: 0.85rem;">
          🏠 Bosh sahifaga qaytish
        </a>
      </div>
    </div>
  </nav>

  <!-- CHAT AREA -->
  <main class="chat-container">
    <div class="chat-card">
      <!-- Chat Header -->
      <div class="chat-header">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: white;">
            🛡️
          </div>
          <div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark); margin: 0;">Oromgo Administratsiyasi</h3>
            <span style="font-size: 0.8rem; color: #16a34a; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
              <span style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%;"></span> Jonli aloqa & Yordam
            </span>
          </div>
        </div>

        <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;" onclick="loadMessages(true)">
          🔄 Yangilash
        </button>
      </div>

      <!-- If Logged in: Chat Messages -->
      <div id="chatBodyContainer" class="chat-body" style="display: none;">
        <!-- Injected via JS -->
      </div>

      <!-- If Logged in: Chat Input Form -->
      <div id="chatFooterContainer" class="chat-footer" style="display: none;">
        <form id="chatForm" onsubmit="handleSendMessage(event)" style="display: flex; gap: 0.75rem; align-items: center;">
          <input type="text" id="chatInput" class="form-control" placeholder="Xabaringiz yoki savolingizni yozing..." required autocomplete="off" style="padding: 0.85rem 1.25rem; font-size: 0.95rem; border-radius: var(--radius-full);" />
          <button type="submit" id="chatSendBtn" class="btn btn-primary" style="border-radius: var(--radius-full); padding: 0.85rem 1.5rem; font-weight: 700; white-space: nowrap;">
            <span>Yuborish</span> ✈️
          </button>
        </form>
      </div>

      <!-- If Guest: Login Required Message -->
      <div id="authRequiredBox" class="auth-overlay">
        <div style="font-size: 3.5rem; margin-bottom: 0.5rem;">💬</div>
        <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--dark); margin-bottom: 0.5rem;">Adminga murojaat qilish</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 480px; margin-bottom: 1.5rem;">
          Savol va takliflaringizni yuborish hamda administrator javobini olish uchun avval profilingizga kiring:
        </p>

        <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%; max-width: 320px;">
          <button class="btn btn-primary" onclick="window.location.href='/?action=login'" style="padding: 0.85rem; font-weight: 700;">
            🔑 Tizimga kirish
          </button>
          <button class="btn btn-outline" onclick="demoLoginAndChat('user')" style="background: var(--bg-page); border-style: dashed; padding: 0.75rem;">
            👤 Mijoz sifatida sinab ko'rish
          </button>
          <button class="btn btn-outline" onclick="demoLoginAndChat('owner')" style="background: var(--bg-page); border-style: dashed; padding: 0.75rem;">
            🏡 Dacha egasi sifatida sinab ko'rish
          </button>
        </div>
      </div>
    </div>
  </main>

  <script>
    const API_BASE = '/api';
    let token = localStorage.getItem('oromgo_token') || '';
    let user = JSON.parse(localStorage.getItem('oromgo_user') || 'null');
    let pollingInterval = null;

    document.addEventListener('DOMContentLoaded', () => {
      checkAuthAndInit();
    });

    function checkAuthAndInit() {
      token = localStorage.getItem('oromgo_token') || '';
      user = JSON.parse(localStorage.getItem('oromgo_user') || 'null');

      const authBox = document.getElementById('authRequiredBox');
      const chatBody = document.getElementById('chatBodyContainer');
      const chatFooter = document.getElementById('chatFooterContainer');

      if (token && user) {
        authBox.style.display = 'none';
        chatBody.style.display = 'flex';
        chatFooter.style.display = 'block';

        loadMessages(true);
        startPolling();
      } else {
        authBox.style.display = 'flex';
        chatBody.style.display = 'none';
        chatFooter.style.display = 'none';
      }
    }

    async function demoLoginAndChat(role) {
      try {
        const res = await fetch(`${API_BASE}/demo-login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ role })
        });
        if (res.ok) {
          const data = await res.json();
          localStorage.setItem('oromgo_token', data.token);
          localStorage.setItem('oromgo_user', JSON.stringify(data.user));
          checkAuthAndInit();
        }
      } catch (err) {
        alert('Kirishda xatolik yuz berdi');
      }
    }

    async function loadMessages(scrollToBottom = false) {
      if (!token) return;
      try {
        const res = await fetch(`${API_BASE}/support/messages`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (res.ok) {
          const messages = await res.json();
          renderMessages(messages, scrollToBottom);
        }
      } catch (err) {
        console.error('loadMessages error:', err);
      }
    }

    function renderMessages(messages, scrollToBottom = false) {
      const container = document.getElementById('chatBodyContainer');

      if (messages.length === 0) {
        container.innerHTML = `
          <div style="text-align: center; margin: auto; padding: 2rem; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👋</div>
            <h4 style="color: var(--dark); font-weight: 800;">Assalomu alaykum!</h4>
            <p style="font-size: 0.9rem; margin-top: 0.25rem;">
              Savol, taklif yoki shikoyatingiz bormi? Pastdagi maydonga yozing, administrator tez orada javob qaytaradi.
            </p>
          </div>
        `;
        return;
      }

      container.innerHTML = messages.map(m => {
        const isUser = m.sender_type === 'user';
        const date = new Date(m.created_at);
        const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        return `
          <div class="chat-bubble ${isUser ? 'chat-bubble-user' : 'chat-bubble-admin'}">
            ${!isUser ? `<div style="font-size: 0.75rem; font-weight: 800; color: #7c3aed; margin-bottom: 0.2rem;">🛡️ Administrator</div>` : ''}
            <div>${escapeHtml(m.message)}</div>
            <div class="chat-time">${timeStr} ${isUser ? '✓' : ''}</div>
          </div>
        `;
      }).join('');

      if (scrollToBottom) {
        container.scrollTop = container.scrollHeight;
      }
    }

    async function handleSendMessage(e) {
      e.preventDefault();
      const input = document.getElementById('chatInput');
      const text = input.value.trim();
      if (!text || !token) return;

      const btn = document.getElementById('chatSendBtn');
      btn.disabled = true;
      input.value = '';

      try {
        const res = await fetch(`${API_BASE}/support/messages`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ message: text })
        });

        if (res.ok) {
          await loadMessages(true);
        } else {
          alert('Xabar yuborishda xatolik yuz berdi');
        }
      } catch (err) {
        alert('Server bilan bog\'lanishda xatolik');
      } finally {
        btn.disabled = false;
        input.focus();
      }
    }

    function startPolling() {
      if (pollingInterval) clearInterval(pollingInterval);
      pollingInterval = setInterval(() => {
        loadMessages(false);
      }, 4000);
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
