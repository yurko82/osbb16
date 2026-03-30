/* ==========================================
   МійДім ОСББ — app.js
   ========================================== */

// ---- DB (localStorage) ----
const DB = {
  get: (key, def = []) => {
    try { return JSON.parse(localStorage.getItem(key)) ?? def; }
    catch { return def; }
  },
  set: (key, val) => localStorage.setItem(key, JSON.stringify(val)),
  getObj: (key, def = {}) => {
    try { return JSON.parse(localStorage.getItem(key)) ?? def; }
    catch { return def; }
  }
};

// Seed demo data if empty
(function seedData() {
  if (!DB.get('osbb_users').length) {
    DB.set('osbb_users', [
      { id: 1, name: 'Адміністратор ОСББ', email: 'admin@mydom.ua', pass: 'admin123', role: 'admin', apt: 1, floor: 1, phone: '+380 44 000 00 00' },
      { id: 2, name: 'Іваненко Іван Іванович', email: 'ivan@test.ua', pass: '123456', role: 'resident', apt: 42, floor: 5, phone: '+380 50 111 22 33' },
      { id: 3, name: 'Петренко Олена Василівна', email: 'olena@test.ua', pass: '123456', role: 'resident', apt: 15, floor: 2, phone: '+380 67 222 33 44' },
      { id: 4, name: 'Коваленко Микола Петрович', email: 'mykola@test.ua', pass: '123456', role: 'resident', apt: 78, floor: 9, phone: '+380 63 333 44 55' },
      { id: 5, name: 'Бондаренко Тетяна Олексіївна', email: 'tanya@test.ua', pass: '123456', role: 'resident', apt: 31, floor: 4, phone: '+380 95 444 55 66' },
      { id: 6, name: 'Марченко Сергій Іванович', email: 'serhiy@test.ua', pass: '123456', role: 'resident', apt: 56, floor: 7, phone: '+380 50 555 66 77' },
    ]);
  }

  if (!DB.get('osbb_news').length) {
    DB.set('osbb_news', [
      { id: 1, title: 'Планові роботи з водопостачання', body: 'Повідомляємо, що 16 квітня з 09:00 до 17:00 буде відключена холодна вода у зв\'язку з плановими ремонтними роботами на магістральному трубопроводі. Просимо завчасно запастись водою.', type: 'warn', date: '2025-04-14', author: 'Адміністратор ОСББ' },
      { id: 2, title: 'Загальні збори ОСББ — 20 квітня', body: 'Запрошуємо всіх мешканців на загальні збори ОСББ, які відбудуться 20 квітня 2025 року о 18:00 в актовому залі будинку (1-й поверх). Порядок денний: затвердження бюджету на 2025 рік, ремонт даху.', type: 'event', date: '2025-04-12', author: 'Адміністратор ОСББ' },
      { id: 3, title: 'Ремонт ліфту №2 завершено', body: 'Повідомляємо про успішне завершення ремонту ліфту №2 у під\'їзді. Ліфт повністю справний та введений в експлуатацію. Дякуємо за розуміння.', type: 'info', date: '2025-04-10', author: 'Адміністратор ОСББ' },
      { id: 4, title: 'Оновлено тарифи на опалення', body: 'З 1 травня 2025 року вводяться нові тарифи на опалення. Детальна інформація розміщена на дошці оголошень у кожному під\'їзді. Квитанції за квітень виставлені за старими тарифами.', type: 'info', date: '2025-04-08', author: 'Адміністратор ОСББ' },
    ]);
  }

  if (!DB.get('osbb_requests').length) {
    DB.set('osbb_requests', [
      { id: 1, userId: 2, apt: 42, cat: 'Сантехніка', desc: 'Протікає вікно у кімнаті — сильний протяг при вітрі', priority: 'mid', status: 'progress', date: '2025-04-08' },
      { id: 2, userId: 2, apt: 42, cat: 'Електрика', desc: 'Не горить лампочка в під\'їзді на 5-му поверсі', priority: 'low', status: 'done', date: '2025-04-05' },
      { id: 3, userId: 3, apt: 15, cat: 'Ліфт', desc: 'Ліфт не зупиняється на 2-му поверсі', priority: 'high', status: 'new', date: '2025-04-13' },
      { id: 4, userId: 5, apt: 31, cat: 'Опалення', desc: 'Холодні батареї в усіх кімнатах', priority: 'high', status: 'progress', date: '2025-04-11' },
    ]);
  }
})();

// ---- Auth ----
function getCurrentUser() {
  return DB.getObj('osbb_current_user', null);
}

function registerUser() {
  const name = document.getElementById('reg-name')?.value.trim();
  const apt = parseInt(document.getElementById('reg-apt')?.value);
  const floor = parseInt(document.getElementById('reg-floor')?.value);
  const phone = document.getElementById('reg-phone')?.value.trim();
  const email = document.getElementById('reg-email')?.value.trim();
  const pass = document.getElementById('reg-pass')?.value;
  const role = document.getElementById('reg-role')?.value;
  const agree = document.getElementById('reg-agree')?.checked;
  const msg = document.getElementById('reg-msg');

  if (!name || !email || !pass || !apt) {
    showMsg(msg, 'Заповніть всі обов\'язкові поля', 'error'); return;
  }
  if (pass.length < 6) { showMsg(msg, 'Пароль мінімум 6 символів', 'error'); return; }
  if (!agree) { showMsg(msg, 'Погодьтесь з умовами використання', 'error'); return; }

  const users = DB.get('osbb_users');
  if (users.find(u => u.email === email)) {
    showMsg(msg, 'Користувач з таким email вже існує', 'error'); return;
  }

  const newUser = { id: Date.now(), name, apt, floor, phone, email, pass, role };
  users.push(newUser);
  DB.set('osbb_users', users);
  DB.set('osbb_current_user', newUser);

  showMsg(msg, '✅ Реєстрація успішна! Переходимо...', 'success');
  setTimeout(() => window.location.href = 'dashboard.html', 1200);
}

function loginUser() {
  const email = document.getElementById('login-email')?.value.trim();
  const pass = document.getElementById('login-pass')?.value;
  const msg = document.getElementById('login-msg');

  if (!email || !pass) { showMsg(msg, 'Введіть email і пароль', 'error'); return; }

  const users = DB.get('osbb_users');
  const user = users.find(u => u.email === email && u.pass === pass);

  if (!user) { showMsg(msg, 'Невірний email або пароль', 'error'); return; }

  DB.set('osbb_current_user', user);
  showMsg(msg, '✅ Вхід успішний! Переходимо...', 'success');
  setTimeout(() => window.location.href = 'dashboard.html', 1000);
}

function logout() {
  localStorage.removeItem('osbb_current_user');
  window.location.href = 'index.html';
}

// ---- Dashboard ----
function initDashboard() {
  const user = getCurrentUser();
  if (!user) { window.location.href = 'login.html'; return; }

  // Fill user info
  const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

  const firstName = user.name.split(' ')[1] || user.name.split(' ')[0];
  setEl('dash-firstname', firstName);
  setEl('dash-name', user.name);
  setEl('dash-apt', `кв. ${user.apt}${user.floor ? ' • ' + user.floor + ' пов.' : ''}`);
  setEl('dash-role', user.role === 'admin' ? '🔑 Адміністратор' : '🏠 Мешканець');

  const ava = document.getElementById('dash-avatar');
  if (ava) ava.textContent = user.name[0].toUpperCase();

  const dateEl = document.getElementById('dash-date');
  if (dateEl) {
    const d = new Date();
    dateEl.textContent = d.toLocaleDateString('uk-UA', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  }
}

// ---- Requests Page ----
function initRequests() {
  renderRequests('all');
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderRequests(btn.dataset.filter);
    });
  });
}

function renderRequests(filter) {
  const user = getCurrentUser();
  const all = DB.get('osbb_requests');
  const container = document.getElementById('req-cards');
  if (!container) return;

  let reqs = user?.role === 'admin' ? all : all.filter(r => r.userId === user?.id);
  if (filter !== 'all') reqs = reqs.filter(r => r.status === filter);
  reqs = reqs.sort((a, b) => b.id - a.id);

  const icons = { Сантехніка:'🚿', Електрика:'⚡', Ліфт:'🛗', 'Під\'їзд / Дах':'🏘️', Опалення:'🔥', Інше:'🔧' };
  const statusLabel = { new:'Нова', progress:'В роботі', done:'Виконано' };
  const statusClass = { new:'status-new', progress:'status-progress', done:'status-done' };
  const prioLabel = { high:'🔴 Терміново', mid:'🟡 Середній', low:'🟢 Низький' };
  const prioClass = { high:'rc-priority-high', mid:'rc-priority-mid', low:'rc-priority-low' };

  if (!reqs.length) {
    container.innerHTML = '<div style="text-align:center;padding:48px;color:#9ca3af;">Заявок не знайдено</div>';
    return;
  }

  container.innerHTML = reqs.map(r => `
    <div class="req-card">
      <div class="rc-icon">${icons[r.cat] || '🔧'}</div>
      <div class="rc-body">
        <div class="rc-title">${r.cat}</div>
        <div class="rc-meta">кв. ${r.apt} • ${formatDate(r.date)}</div>
        <div class="rc-desc">${r.desc}</div>
      </div>
      <div class="rc-right">
        <span class="req-status ${statusClass[r.status]}">${statusLabel[r.status]}</span>
        <span class="${prioClass[r.priority]}">${prioLabel[r.priority]}</span>
        ${user?.role === 'admin' ? `<button class="btn-outline" style="padding:4px 10px;font-size:0.76rem;margin-top:4px" onclick="changeStatus(${r.id})">Змінити статус</button>` : ''}
      </div>
    </div>
  `).join('');
}

function submitRequest() {
  const user = getCurrentUser();
  if (!user) return;
  const cat = document.getElementById('req-cat')?.value;
  const desc = document.getElementById('req-desc')?.value.trim();
  const priority = document.getElementById('req-priority')?.value;

  if (!desc) { showToast('Опишіть проблему', 'error'); return; }

  const reqs = DB.get('osbb_requests');
  reqs.push({ id: Date.now(), userId: user.id, apt: user.apt, cat, desc, priority, status: 'new', date: new Date().toISOString().split('T')[0] });
  DB.set('osbb_requests', reqs);
  closeModal('req-modal');
  showToast('✅ Заявку подано успішно!', 'success');
  renderRequests('all');
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  document.querySelector('.filter-btn[data-filter="all"]')?.classList.add('active');
}

function changeStatus(id) {
  const reqs = DB.get('osbb_requests');
  const idx = reqs.findIndex(r => r.id === id);
  if (idx < 0) return;
  const cycle = { new:'progress', progress:'done', done:'new' };
  reqs[idx].status = cycle[reqs[idx].status];
  DB.set('osbb_requests', reqs);
  const activeFilter = document.querySelector('.filter-btn.active')?.dataset.filter || 'all';
  renderRequests(activeFilter);
  showToast('Статус оновлено');
}

// ---- News Page ----
function initNews() {
  const user = getCurrentUser();
  const adminBtn = document.getElementById('admin-add-news');
  if (adminBtn && user?.role === 'admin') adminBtn.classList.remove('hidden');
  renderNews();
}

function renderNews() {
  const news = DB.get('osbb_news').sort((a, b) => b.id - a.id);
  const container = document.getElementById('news-cards');
  if (!container) return;

  const typeLabel = { info:'Інформація', warn:'Попередження', event:'Подія' };
  const typeClass = { info:'nc-info', warn:'nc-warn', event:'nc-event' };

  container.innerHTML = news.map(n => `
    <div class="news-card">
      <div class="nc-head">
        <span class="nc-type ${typeClass[n.type]}">${typeLabel[n.type]}</span>
        <span class="nc-date">${formatDate(n.date)}</span>
      </div>
      <div class="nc-title">${n.title}</div>
      <div class="nc-body">${n.body}</div>
    </div>
  `).join('');
}

function addNews() {
  const title = document.getElementById('news-title-inp')?.value.trim();
  const body = document.getElementById('news-body')?.value.trim();
  const type = document.getElementById('news-type')?.value;
  if (!title || !body) { showToast('Заповніть всі поля', 'error'); return; }

  const user = getCurrentUser();
  const news = DB.get('osbb_news');
  news.push({ id: Date.now(), title, body, type, date: new Date().toISOString().split('T')[0], author: user?.name });
  DB.set('osbb_news', news);
  closeModal('news-modal');
  showToast('✅ Оголошення опубліковано!', 'success');
  renderNews();
}

// ---- Residents Page ----
let allResidents = [];
function initResidents() {
  allResidents = DB.get('osbb_users').filter(u => u.role !== 'admin');
  renderResidents(allResidents);
}

function filterResidents() {
  const q = document.getElementById('res-search')?.value.toLowerCase() || '';
  const filtered = allResidents.filter(u =>
    u.name.toLowerCase().includes(q) || String(u.apt).includes(q)
  );
  renderResidents(filtered);
}

const COLORS = ['#6366f1','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899'];
function renderResidents(list) {
  const container = document.getElementById('res-grid');
  if (!container) return;
  if (!list.length) { container.innerHTML = '<div style="text-align:center;padding:40px;color:#9ca3af;">Нікого не знайдено</div>'; return; }
  container.innerHTML = list.map((u, i) => `
    <div class="res-card">
      <div class="res-ava" style="background:${COLORS[i % COLORS.length]}">${u.name[0].toUpperCase()}</div>
      <div class="res-info">
        <div class="res-name">${u.name}</div>
        <div class="res-apt">кв. ${u.apt} ${u.floor ? '• ' + u.floor + ' пов.' : ''}</div>
        ${u.phone ? `<div class="res-apt" style="margin-top:2px">${u.phone}</div>` : ''}
      </div>
      <span class="res-status badge-paid">Мешканець</span>
    </div>
  `).join('');
}

// ---- Payments ----
function showPayModal() {
  const user = getCurrentUser();
  const el = document.getElementById('pay-apt-info');
  if (el && user) el.textContent = `кв. ${user.apt}`;
  openModal('pay-modal');
}

function confirmPayment() {
  closeModal('pay-modal');
  showToast('✅ Оплату здійснено успішно! Дякуємо.', 'success');
}

// ---- Navbar ----
function initNavbar() {
  // Scroll shadow
  window.addEventListener('scroll', () => {
    document.getElementById('navbar')?.classList.toggle('scrolled', window.scrollY > 10);
  });

  // Burger
  const burger = document.getElementById('burger');
  const navLinks = document.getElementById('navLinks');
  if (burger && navLinks) {
    burger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
    // Close on link click
    navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('open')));
  }

  // User info in nav
  const user = getCurrentUser();
  if (user) {
    // Sidebar user info
    const dashName = document.getElementById('dash-name');
    if (dashName) dashName.textContent = user.name;
    const dashApt = document.getElementById('dash-apt');
    if (dashApt) dashApt.textContent = `кв. ${user.apt}`;
  }
}

// ---- Auth tabs ----
function switchTab(tab) {
  document.querySelectorAll('.auth-tab').forEach((t, i) => {
    const forms = ['register','login'];
    t.classList.toggle('active', forms[i] === tab);
  });
  document.querySelectorAll('.auth-form').forEach(f => {
    f.classList.toggle('hidden', !f.id.includes(tab));
  });
}

document.querySelectorAll('.auth-tab[data-tab]').forEach(btn => {
  btn.addEventListener('click', () => switchTab(btn.dataset.tab));
});

// ---- Helpers ----
function togglePass(id, btn) {
  const input = document.getElementById(id);
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
  btn.textContent = input.type === 'password' ? '👁' : '🙈';
}

function openModal(id) {
  document.getElementById(id)?.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  document.getElementById(id)?.classList.add('hidden');
  document.body.style.overflow = '';
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) closeModal(overlay.id);
  });
});

function showMsg(el, text, type) {
  if (!el) return;
  el.textContent = text;
  el.className = `form-msg ${type}`;
}

function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.style.opacity = '0', 2500);
  setTimeout(() => t.remove(), 3000);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('uk-UA', { day:'numeric', month:'long', year:'numeric' });
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  const page = window.location.pathname.split('/').pop();
  if (page === 'dashboard.html') initDashboard();
  if (page === 'requests.html') { initDashboard(); initRequests(); }
  if (page === 'news.html') { initDashboard(); initNews(); }
  if (page === 'residents.html') { initDashboard(); initResidents(); }
  if (page === 'payments.html') initDashboard();
});
