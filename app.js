// ============================================================
//  МійДім ОСББ — app.js (Google Sheets версія)
// ============================================================

// !! Замінити на URL після деплою Apps Script !!
const API_BASE =
  "https://script.google.com/macros/s/AKfycbxzpGz3K8YOQFMNNOZ3IHqgM7jFRiY-2OB0NS0sL43Q6HAW3vGbi4_fNzdyGyl2QD3k/exec";

// ============================================================
//  API CLIENT
// ============================================================
const API = {
  _token() {
    return localStorage.getItem("osbb_token") || "";
  },

  async call(path, action, method = "GET", body = null, extra = {}) {
    const params = new URLSearchParams({
      path,
      action,
      token: this._token(),
      ...extra,
    });
    const url = `${API_BASE}?${params}`;
    const opts = { method, redirect: "follow" };

    if (body && method === "POST") {
      opts.headers = { "Content-Type": "text/plain" }; // Apps Script вимагає text/plain
      opts.body = JSON.stringify({ ...body, token: this._token() });
    }

    try {
      const res = await fetch(url, opts);
      const data = await res.json();
      if (!data.success) throw new Error(data.message || "Помилка");
      return data.data;
    } catch (e) {
      throw e;
    }
  },

  get: (path, action, params = {}) =>
    API.call(path, action, "GET", null, params),
  post: (path, action, body = {}) => API.call(path, action, "POST", body),
};

// ============================================================
//  AUTH
// ============================================================
async function loginUser() {
  const email = document.getElementById("login-email")?.value.trim();
  const pass = document.getElementById("login-pass")?.value;
  const msg = document.getElementById("login-msg");
  if (!email || !pass) {
    showMsg(msg, "Введіть email і пароль", "error");
    return;
  }

  try {
    showMsg(msg, "⏳ Зачекайте...", "");
    const data = await API.post("auth", "login", { email, password: pass });
    localStorage.setItem("osbb_token", data.token);
    localStorage.setItem("osbb_user", JSON.stringify(data));
    showMsg(msg, "✅ Вхід успішний!", "success");
    setTimeout(() => (window.location.href = "dashboard.html"), 800);
  } catch (e) {
    showMsg(msg, e.message, "error");
  }
}

async function registerUser() {
  const name = document.getElementById("reg-name")?.value.trim();
  const aptId = document.getElementById("reg-apt-select")?.value;
  const phone = document.getElementById("reg-phone")?.value.trim();
  const email = document.getElementById("reg-email")?.value.trim();
  const pass = document.getElementById("reg-pass")?.value;
  const agree = document.getElementById("reg-agree")?.checked;
  const msg = document.getElementById("reg-msg");

  if (!name || !email || !pass || !aptId) {
    showMsg(msg, "Заповніть всі поля", "error");
    return;
  }
  if (!agree) {
    showMsg(msg, "Погодьтесь з умовами", "error");
    return;
  }

  try {
    showMsg(msg, "⏳ Реєстрація...", "");
    const data = await API.post("auth", "register", {
      full_name: name,
      email,
      password: pass,
      apartment_id: aptId,
      phone,
    });
    localStorage.setItem("osbb_token", data.token);
    localStorage.setItem("osbb_user", JSON.stringify(data));
    showMsg(msg, "✅ Готово! Переходимо...", "success");
    setTimeout(() => (window.location.href = "dashboard.html"), 1000);
  } catch (e) {
    showMsg(msg, e.message, "error");
  }
}

function logout() {
  localStorage.removeItem("osbb_token");
  localStorage.removeItem("osbb_user");
  window.location.href = "index.html";
}

function getCurrentUser() {
  try {
    return JSON.parse(localStorage.getItem("osbb_user"));
  } catch {
    return null;
  }
}

// ============================================================
//  DASHBOARD
// ============================================================
async function initDashboard() {
  const user = getCurrentUser();
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  fillUserUI(user);

  try {
    const now = new Date();
    const data = await API.get("payments", "summary", {
      year: now.getFullYear(),
      month: now.getMonth() + 1,
    });
    fillDashboardKPI(data, user);
  } catch (e) {
    console.warn("Summary error:", e);
  }

  // Завантажити новини для дашборду
  try {
    const news = await API.get("news", "list");
    renderDashNews(news.slice(0, 3));
  } catch (e) {}

  // Завантажити заявки
  try {
    const reqs = await API.get("requests", "list");
    renderDashRequests(reqs.slice(0, 3));
  } catch (e) {}
}

function fillUserUI(user) {
  const setEl = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };
  const firstName =
    (user.name || "").split(" ")[1] || (user.name || "").split(" ")[0];
  setEl("dash-firstname", firstName || "Користувач");
  setEl("dash-name", user.name || "");
  setEl(
    "dash-role",
    user.role === "admin" ? "🔑 Адміністратор" : "🏠 Мешканець",
  );

  const dateEl = document.getElementById("dash-date");
  if (dateEl)
    dateEl.textContent = new Date().toLocaleDateString("uk-UA", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });

  const ava = document.getElementById("dash-avatar");
  if (ava && user.name) ava.textContent = user.name[0].toUpperCase();
}

function fillDashboardKPI(data, user) {
  const setEl = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };

  if (user.role === "resident") {
    setEl("kpi-debt", `₴ ${Number(data.balance || 0).toFixed(2)}`);
    setEl("kpi-paid", `₴ ${Number(data.paid || 0).toFixed(2)}`);
    setEl("kpi-requests", data.open_requests || 0);
  } else {
    setEl("kpi-debt", `₴ ${Number(data.total_debt || 0).toFixed(2)}`);
    setEl("kpi-paid", `₴ ${Number(data.total_paid || 0).toFixed(2)}`);
    setEl("kpi-requests", data.open_requests || 0);
    setEl("kpi-pending", data.pending_payments || 0);
    setEl("kpi-residents", data.total_residents || 0);
  }
}

function renderDashNews(news) {
  const el = document.querySelector(".news-list");
  if (!el || !news.length) return;
  const colors = {
    info: "news-blue",
    warning: "news-red",
    event: "news-green",
    urgent: "news-red",
  };
  el.innerHTML = news
    .map(
      (n) => `
    <div class="news-item">
      <div class="news-dot ${colors[n.type] || "news-blue"}"></div>
      <div>
        <div class="news-title">${n.title}</div>
        <div class="news-date">${formatDate(n.created_at)}</div>
      </div>
    </div>
  `,
    )
    .join("");
}

function renderDashRequests(reqs) {
  const el = document.getElementById("dash-req-list");
  if (!el) return;
  const icons = {
    plumbing: "🚿",
    electricity: "⚡",
    elevator: "🛗",
    roof: "🏘️",
    heating: "🔥",
    other: "🔧",
  };
  const statusClass = {
    new: "status-new",
    in_progress: "status-progress",
    done: "status-done",
    assigned: "status-progress",
  };
  const statusLabel = {
    new: "Нова",
    in_progress: "В роботі",
    done: "Виконано",
    assigned: "Призначено",
    rejected: "Відхилено",
  };

  if (!reqs.length) {
    el.innerHTML =
      '<div style="color:#9ca3af;font-size:.85rem;padding:8px 0">Немає активних заявок</div>';
    return;
  }

  el.innerHTML = reqs
    .map(
      (r) => `
    <div class="req-item">
      <div class="req-icon">${icons[r.category] || "🔧"}</div>
      <div class="req-info">
        <div class="req-title">${r.title}</div>
        <div class="req-date">${formatDate(r.created_at)}</div>
      </div>
      <span class="req-status ${statusClass[r.status] || "status-new"}">${statusLabel[r.status] || r.status}</span>
    </div>
  `,
    )
    .join("");
}

// ============================================================
//  RESIDENTS PAGE
// ============================================================
let allResidentsData = [];

async function initResidents() {
  fillUserUI(getCurrentUser() || {});
  showSkeleton("res-grid", 6);
  try {
    allResidentsData = await API.get("residents", "list");
    renderResidents(allResidentsData);
  } catch (e) {
    document.getElementById("res-grid").innerHTML =
      `<div class="error-msg">${e.message}</div>`;
  }
}

function filterResidents() {
  const q = (document.getElementById("res-search")?.value || "").toLowerCase();
  renderResidents(
    q
      ? allResidentsData.filter(
          (r) =>
            String(r.full_name).toLowerCase().includes(q) ||
            String(r.apartment_number).includes(q),
        )
      : allResidentsData,
  );
}

const COLORS = [
  "#6366f1",
  "#8b5cf6",
  "#06b6d4",
  "#10b981",
  "#f59e0b",
  "#ef4444",
  "#ec4899",
];
function renderResidents(list) {
  const el = document.getElementById("res-grid");
  if (!el) return;
  if (!list.length) {
    el.innerHTML =
      '<div style="text-align:center;padding:40px;color:#9ca3af;">Нікого не знайдено</div>';
    return;
  }
  el.innerHTML = list
    .map(
      (r, i) => `
    <div class="res-card">
      <div class="res-ava" style="background:${COLORS[i % COLORS.length]}">${String(r.full_name)[0]?.toUpperCase() || "?"}</div>
      <div class="res-info">
        <div class="res-name">${r.full_name}</div>
        <div class="res-apt">кв. ${r.apartment_number}</div>
        ${r.phone ? `<div class="res-apt">${r.phone}</div>` : ""}
      </div>
      <span class="res-status badge-paid">Мешканець</span>
    </div>
  `,
    )
    .join("");
}

// ============================================================
//  REQUESTS PAGE
// ============================================================
async function initRequests() {
  fillUserUI(getCurrentUser() || {});
  await loadRequests("all");

  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".filter-btn")
        .forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      loadRequests(btn.dataset.filter);
    });
  });
}

async function loadRequests(filter) {
  const el = document.getElementById("req-cards");
  if (!el) return;
  el.innerHTML =
    '<div style="text-align:center;padding:32px;color:#9ca3af">⏳ Завантаження...</div>';
  try {
    const params = filter !== "all" ? { status: filter } : {};
    const reqs = await API.get("requests", "list", params);
    renderRequests(reqs);
  } catch (e) {
    el.innerHTML = `<div class="error-msg">${e.message}</div>`;
  }
}

function renderRequests(reqs) {
  const el = document.getElementById("req-cards");
  if (!el) return;
  const icons = {
    plumbing: "🚿",
    electricity: "⚡",
    elevator: "🛗",
    roof: "🏘️",
    entrance: "🚪",
    heating: "🔥",
    other: "🔧",
  };
  const catLabel = {
    plumbing: "Сантехніка",
    electricity: "Електрика",
    elevator: "Ліфт",
    roof: "Дах/Покрівля",
    entrance: "Під'їзд",
    heating: "Опалення",
    other: "Інше",
  };
  const statusLabel = {
    new: "Нова",
    assigned: "Призначено",
    in_progress: "В роботі",
    done: "Виконано",
    rejected: "Відхилено",
  };
  const statusClass = {
    new: "status-new",
    assigned: "status-progress",
    in_progress: "status-progress",
    done: "status-done",
    rejected: "status-done",
  };
  const prioLabel = {
    low: "🟢 Низький",
    medium: "🟡 Середній",
    high: "🔴 Терміново",
    urgent: "🆘 Критично",
  };
  const prioClass = {
    low: "rc-priority-low",
    medium: "rc-priority-mid",
    high: "rc-priority-high",
    urgent: "rc-priority-high",
  };

  if (!reqs.length) {
    el.innerHTML =
      '<div style="text-align:center;padding:48px;color:#9ca3af;">Заявок не знайдено</div>';
    return;
  }
  const user = getCurrentUser();

  el.innerHTML = reqs
    .map(
      (r) => `
    <div class="req-card">
      <div class="rc-icon">${icons[r.category] || "🔧"}</div>
      <div class="rc-body">
        <div class="rc-title">${catLabel[r.category] || r.category}: ${r.title}</div>
        <div class="rc-meta">кв. ${r.apartment_number || "—"} • ${formatDate(r.created_at)}</div>
        ${r.description ? `<div class="rc-desc">${r.description}</div>` : ""}
      </div>
      <div class="rc-right">
        <span class="req-status ${statusClass[r.status] || "status-new"}">${statusLabel[r.status] || r.status}</span>
        <span class="${prioClass[r.priority] || "rc-priority-mid"}">${prioLabel[r.priority] || r.priority}</span>
        ${
          user?.role === "admin"
            ? `
          <select class="req-status-sel" onchange="changeReqStatus('${r.id}', this.value)" style="margin-top:6px;font-size:.76rem;padding:4px;border-radius:8px;border:1px solid #e5e7f0">
            <option value="new"         ${r.status === "new" ? "selected" : ""}>Нова</option>
            <option value="in_progress" ${r.status === "in_progress" ? "selected" : ""}>В роботі</option>
            <option value="done"        ${r.status === "done" ? "selected" : ""}>Виконано</option>
            <option value="rejected"    ${r.status === "rejected" ? "selected" : ""}>Відхилено</option>
          </select>
        `
            : ""
        }
      </div>
    </div>
  `,
    )
    .join("");
}

async function submitRequest() {
  const cat = document.getElementById("req-cat")?.value;
  const desc = document.getElementById("req-desc")?.value.trim();
  const prio = document.getElementById("req-priority")?.value;
  if (!desc) {
    showToast("Опишіть проблему", "error");
    return;
  }

  try {
    await API.post("requests", "create", {
      category: cat,
      title: desc.substring(0, 80),
      description: desc,
      priority: prio,
    });
    closeModal("req-modal");
    showToast("✅ Заявку подано!", "success");
    const activeFilter =
      document.querySelector(".filter-btn.active")?.dataset.filter || "all";
    loadRequests(activeFilter);
  } catch (e) {
    showToast(e.message, "error");
  }
}

async function changeReqStatus(id, status) {
  try {
    await API.call("requests", "update", "POST", { status }, { id });
    showToast("Статус оновлено");
  } catch (e) {
    showToast(e.message, "error");
  }
}

// ============================================================
//  NEWS PAGE
// ============================================================
async function initNews() {
  const user = getCurrentUser();
  fillUserUI(user || {});
  const adminBtn = document.getElementById("admin-add-news");
  if (adminBtn && user?.role === "admin") adminBtn.classList.remove("hidden");

  showSkeleton("news-cards", 3, "news");
  try {
    const news = await API.get("news", "list");
    renderNewsCards(news);
  } catch (e) {
    document.getElementById("news-cards").innerHTML =
      `<div class="error-msg">${e.message}</div>`;
  }
}

function renderNewsCards(news) {
  const el = document.getElementById("news-cards");
  if (!el) return;
  const typeLabel = {
    info: "Інформація",
    warning: "Попередження",
    event: "Подія",
    urgent: "Терміново",
  };
  const typeClass = {
    info: "nc-info",
    warning: "nc-warn",
    event: "nc-event",
    urgent: "nc-warn",
  };
  if (!news.length) {
    el.innerHTML =
      '<div style="text-align:center;padding:40px;color:#9ca3af;">Новин немає</div>';
    return;
  }
  el.innerHTML = news
    .map(
      (n) => `
    <div class="news-card">
      <div class="nc-head">
        <span class="nc-type ${typeClass[n.type] || "nc-info"}">${typeLabel[n.type] || n.type}</span>
        <span class="nc-date">${formatDate(n.created_at)}</span>
      </div>
      <div class="nc-title">${n.title}</div>
      <div class="nc-body">${n.body}</div>
      ${n.author_name ? `<div style="font-size:.75rem;color:#9ca3af;margin-top:8px">— ${n.author_name}</div>` : ""}
    </div>
  `,
    )
    .join("");
}

async function addNews() {
  const title = document.getElementById("news-title-inp")?.value.trim();
  const body = document.getElementById("news-body")?.value.trim();
  const type = document.getElementById("news-type")?.value;
  if (!title || !body) {
    showToast("Заповніть всі поля", "error");
    return;
  }
  try {
    await API.post("news", "create", { title, body, type });
    closeModal("news-modal");
    showToast("✅ Оголошення опубліковано!", "success");
    const news = await API.get("news", "list");
    renderNewsCards(news);
  } catch (e) {
    showToast(e.message, "error");
  }
}

// ============================================================
//  PAYMENTS PAGE
// ============================================================
async function initPayments() {
  const user = getCurrentUser();
  fillUserUI(user || {});
  const now = new Date();
  try {
    const data = await API.get("meters", "charges", {
      year: now.getFullYear(),
      month: now.getMonth() + 1,
    });
    renderPaymentDetails(data);
  } catch (e) {
    showToast(e.message, "error");
  }

  try {
    const history = await API.get("payments", "list", {
      year: now.getFullYear(),
    });
    renderPaymentHistory(history);
  } catch (e) {}
}

function renderPaymentDetails(data) {
  const amtEl = document.querySelector(".pay-amount");
  if (amtEl) amtEl.textContent = `₴ ${Number(data.balance || 0).toFixed(2)}`;

  const svcNames = {
    cold_water: "💧 Холодна вода",
    hot_water: "🔥 Гаряча вода",
    electricity: "⚡ Електрика",
    gas: "🔵 Газ",
    maintenance: "🏠 Утримання",
    garbage: "🗑️ Сміття",
    elevator: "🛗 Ліфт",
  };
  const svcEl = document.querySelector(".pay-services");
  if (svcEl && data.charges) {
    svcEl.innerHTML = data.charges
      .map(
        (c) => `
      <div class="pay-svc">
        <div class="pay-svc-info">
          <span class="pay-svc-icon">${svcNames[c.service_type]?.split(" ")[0] || "💰"}</span>
          <div>
            <div class="pay-svc-name">${svcNames[c.service_type] || c.service_type}</div>
            <div class="pay-svc-detail">${c.volume} × ₴ ${c.tariff_value}</div>
          </div>
        </div>
        <div class="pay-svc-right">
          <div class="pay-svc-sum">₴ ${Number(c.amount).toFixed(2)}</div>
          <span class="${data.payments?.some((p) => p.status === "confirmed") ? "badge-paid" : "badge-pending"}">
            ${data.payments?.some((p) => p.status === "confirmed") ? "Сплачено" : "Очікує"}
          </span>
        </div>
      </div>
    `,
      )
      .join("");
  }
}

function renderPaymentHistory(payments) {
  const tbodyEl = document.querySelector(".data-table tbody");
  if (!tbodyEl || !payments) return;
  const months = [
    "",
    "Січень",
    "Лютий",
    "Березень",
    "Квітень",
    "Травень",
    "Червень",
    "Липень",
    "Серпень",
    "Вересень",
    "Жовтень",
    "Листопад",
    "Грудень",
  ];
  tbodyEl.innerHTML =
    payments
      .map(
        (p) => `
    <tr>
      <td>${months[p.billing_month] || p.billing_month} ${p.billing_year}</td>
      <td>₴ ${Number(p.amount).toFixed(2)}</td>
      <td>${p.confirmed_at ? formatDate(p.confirmed_at) : "—"}</td>
      <td><span class="${p.status === "confirmed" ? "badge-paid" : "badge-pending"}">
        ${p.status === "confirmed" ? "Сплачено" : p.status === "pending" ? "Очікує" : "Відхилено"}
      </span></td>
    </tr>
  `,
      )
      .join("") ||
    '<tr><td colspan="4" style="text-align:center;color:#9ca3af">Немає оплат</td></tr>';
}

async function confirmPayment() {
  const user = getCurrentUser();
  const now = new Date();
  try {
    await API.post("payments", "create", {
      billing_year: now.getFullYear(),
      billing_month: now.getMonth() + 1,
      amount:
        document
          .querySelector(".pay-amount")
          ?.textContent.replace("₴ ", "")
          .trim() || 0,
      payment_method: "online",
    });
    closeModal("pay-modal");
    showToast(
      "✅ Оплату подано! Очікує підтвердження адміністратора.",
      "success",
    );
  } catch (e) {
    showToast(e.message, "error");
  }
}

// ============================================================
//  REGISTER PAGE - завантажити квартири
// ============================================================
async function initRegisterPage() {
  try {
    const apts = await API.get("apartments", "list");
    const sel = document.getElementById("reg-apt-select");
    if (!sel) return;
    sel.innerHTML =
      '<option value="">— Оберіть квартиру —</option>' +
      apts
        .map(
          (a) =>
            `<option value="${a.id}">кв. ${a.number} (${a.floor} пов., ${a.area_m2} м²)</option>`,
        )
        .join("");
  } catch (e) {}
}

// ============================================================
//  NAVBAR
// ============================================================
function initNavbar() {
  window.addEventListener("scroll", () => {
    document
      .getElementById("navbar")
      ?.classList.toggle("scrolled", window.scrollY > 10);
  });
  const burger = document.getElementById("burger");
  const navLinks = document.getElementById("navLinks");
  if (burger && navLinks) {
    burger.addEventListener("click", () => navLinks.classList.toggle("open"));
    navLinks
      .querySelectorAll("a")
      .forEach((a) =>
        a.addEventListener("click", () => navLinks.classList.remove("open")),
      );
  }
}

// ============================================================
//  AUTH TABS
// ============================================================
function switchTab(tab) {
  document.querySelectorAll(".auth-tab[data-tab]").forEach((t) => {
    t.classList.toggle("active", t.dataset.tab === tab);
  });
  document.querySelectorAll(".auth-form").forEach((f) => {
    f.classList.toggle("hidden", !f.id.includes(tab));
  });
}
document.querySelectorAll(".auth-tab[data-tab]").forEach((btn) => {
  btn.addEventListener("click", () => switchTab(btn.dataset.tab));
});

// ============================================================
//  HELPERS
// ============================================================
function showPayModal() {
  const user = getCurrentUser();
  const el = document.getElementById("pay-apt-info");
  if (el && user?.apartment_id)
    el.textContent = `кв. (ID: ${user.apartment_id})`;
  openModal("pay-modal");
}

function openModal(id) {
  document.getElementById(id)?.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}
function closeModal(id) {
  document.getElementById(id)?.classList.add("hidden");
  document.body.style.overflow = "";
}

document.querySelectorAll(".modal-overlay").forEach((o) => {
  o.addEventListener("click", (e) => {
    if (e.target === o) closeModal(o.id);
  });
});

function togglePass(id, btn) {
  const inp = document.getElementById(id);
  if (!inp) return;
  inp.type = inp.type === "password" ? "text" : "password";
  btn.textContent = inp.type === "password" ? "👁" : "🙈";
}

function showMsg(el, text, type) {
  if (!el) return;
  el.textContent = text;
  el.className = `form-msg ${type}`;
}

function showToast(msg, type = "success") {
  const t = document.createElement("div");
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => (t.style.opacity = "0"), 2500);
  setTimeout(() => t.remove(), 3000);
}

function formatDate(str) {
  if (!str) return "";
  try {
    return new Date(str).toLocaleDateString("uk-UA", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  } catch {
    return str;
  }
}

function showSkeleton(containerId, count, type = "card") {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = Array(count)
    .fill(
      `<div style="background:#f0f0ff;border-radius:16px;height:80px;animation:pulse 1.5s ease-in-out infinite"></div>`,
    )
    .join("");
}

// ============================================================
//  INIT
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
  initNavbar();
  const page = window.location.pathname.split("/").pop() || "index.html";
  if (page === "dashboard.html") initDashboard();
  if (page === "requests.html") {
    initDashboard();
    initRequests();
  }
  if (page === "news.html") {
    initDashboard();
    initNews();
  }
  if (page === "residents.html") {
    initDashboard();
    initResidents();
  }
  if (page === "payments.html") {
    initDashboard();
    initPayments();
  }
  if (page === "register.html") initRegisterPage();
});
