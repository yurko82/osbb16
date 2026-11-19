// ============================================================
//  МійДім ОСББ — Google Apps Script (Code.gs)
//  Деплоїти як: Web App → Execute as Me → Anyone
// ============================================================

// ---- ID твоєї Google Таблиці ----
// Після створення таблиці скопіюй ID з URL:
// https://docs.google.com/spreadsheets/d/COPY_THIS_ID/edit
const SPREADSHEET_ID = 'YOUR_SPREADSHEET_ID_HERE';

// JWT секрет (замінити на довільний рядок 32+ символи)
const JWT_SECRET = 'REPLACE_WITH_YOUR_SECRET_KEY_MIN_32_CHARS';

// ============================================================
//  ІНІЦІАЛІЗАЦІЯ — створити всі аркуші якщо не існують
// ============================================================
function initSheets() {
  const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  const sheets = {
    users: ['id','email','password_hash','role','resident_id','apartment_id','full_name','phone','is_active','created_at','last_login'],
    apartments: ['id','number','floor','area_m2','rooms','residents_cnt','building'],
    residents: ['id','apartment_id','apartment_number','full_name','phone','email','is_owner','is_active','notes','created_at'],
    meter_readings: ['id','apartment_id','apartment_number','meter_type','reading_value','consumption','reading_date','entered_by','source'],
    tariffs: ['id','service_type','tariff_value','unit','valid_from'],
    charges: ['id','apartment_id','apartment_number','billing_year','billing_month','service_type','tariff_value','volume','amount','subsidy'],
    payments: ['id','apartment_id','apartment_number','resident_name','billing_year','billing_month','amount','payment_method','status','transaction_id','notes','created_at','confirmed_by','confirmed_at'],
    requests: ['id','apartment_id','apartment_number','user_id','category','title','description','priority','status','assigned_to','resolution','created_at','updated_at'],
    news: ['id','author_id','author_name','title','body','type','is_published','created_at'],
    expenses: ['id','category','description','amount','expense_date','contractor','created_by','created_at'],
  };

  Object.entries(sheets).forEach(([name, headers]) => {
    let sheet = ss.getSheetByName(name);
    if (!sheet) {
      sheet = ss.insertSheet(name);
      sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
      sheet.getRange(1, 1, 1, headers.length)
        .setBackground('#4a4a9c')
        .setFontColor('#ffffff')
        .setFontWeight('bold');
      sheet.setFrozenRows(1);
    }
  });

  // Демо дані
  seedDemoData(ss);
  return { success: true, message: 'Аркуші створено' };
}

function seedDemoData(ss) {
  // Тарифи
  const tarSheet = ss.getSheetByName('tariffs');
  if (getAll('tariffs').length === 0) {
    const tariffs = [
      [genId(), 'cold_water',   30.12, 'м³',    '2025-01-01'],
      [genId(), 'hot_water',    95.40, 'м³',    '2025-01-01'],
      [genId(), 'electricity',   4.32, 'кВт',   '2025-01-01'],
      [genId(), 'gas',           7.99, 'м³',    '2025-01-01'],
      [genId(), 'maintenance',  15.00, 'м²',    '2025-01-01'],
      [genId(), 'garbage',      45.00, 'особа', '2025-01-01'],
      [genId(), 'elevator',     25.00, 'особа', '2025-01-01'],
    ];
    tariffs.forEach(r => tarSheet.appendRow(r));
  }

  // Квартири
  if (getAll('apartments').length === 0) {
    const apts = [
      [genId(),  1, 1, 38.5, 1, 1, 'Будинок 1'],
      [genId(), 15, 2, 45.0, 2, 2, 'Будинок 1'],
      [genId(), 31, 4, 55.6, 2, 3, 'Будинок 1'],
      [genId(), 42, 5, 62.4, 3, 2, 'Будинок 1'],
      [genId(), 56, 7, 44.5, 2, 1, 'Будинок 1'],
      [genId(), 78, 9, 52.1, 2, 2, 'Будинок 1'],
    ];
    const aptSheet = ss.getSheetByName('apartments');
    apts.forEach(r => aptSheet.appendRow(r));
  }

  // Адмін акаунт
  if (getAll('users').length === 0) {
    const usersSheet = ss.getSheetByName('users');
    usersSheet.appendRow([
      genId(), 'admin@mydom.ua', hashPassword('Admin@2025'),
      'admin', '', '', 'Адміністратор ОСББ', '+380 44 000 00 00',
      true, new Date().toISOString(), ''
    ]);
  }

  // Демо новини
  if (getAll('news').length === 0) {
    const newsSheet = ss.getSheetByName('news');
    const demoNews = [
      [genId(), '', 'Адміністратор', 'Планові роботи з водопостачання',
       '16 квітня з 09:00 до 17:00 буде відключена холодна вода у зв\'язку з плановими ремонтними роботами.',
       'warning', true, new Date().toISOString()],
      [genId(), '', 'Адміністратор', 'Загальні збори ОСББ — 20 квітня',
       'Запрошуємо всіх мешканців на загальні збори о 18:00 в актовому залі будинку (1-й поверх).',
       'event', true, new Date().toISOString()],
      [genId(), '', 'Адміністратор', 'Ремонт ліфту №2 завершено',
       'Повідомляємо про успішне завершення ремонту ліфту №2. Ліфт повністю справний.',
       'info', true, new Date().toISOString()],
    ];
    demoNews.forEach(r => newsSheet.appendRow(r));
  }
}

// ============================================================
//  HTTP ROUTER
// ============================================================
function doGet(e) {
  return handleRequest(e);
}

function doPost(e) {
  return handleRequest(e);
}

function handleRequest(e) {
  try {
    const params  = e.parameter || {};
    const path    = params.path || '';
    const action  = params.action || '';
    const method  = e.postData ? 'POST' : 'GET';
    const body    = e.postData ? JSON.parse(e.postData.contents || '{}') : {};

    let result;

    // ---- Auth (публічні) ----
    if (path === 'auth') {
      if (action === 'login')    result = login(body);
      else if (action === 'register') result = register(body);
      else if (action === 'me')  result = getMe(params, e);
      else result = error('Невідома дія');

    // ---- Захищені маршрути ----
    } else {
      const user = verifyToken(params.token || body.token || '');
      if (!user) return jsonResp(error('Не авторизовано', 401));

      if (path === 'residents') result = residentsRouter(action, body, params, user);
      else if (path === 'apartments') result = apartmentsRouter(action, body, params, user);
      else if (path === 'meters')   result = metersRouter(action, body, params, user);
      else if (path === 'payments') result = paymentsRouter(action, body, params, user);
      else if (path === 'requests') result = requestsRouter(action, body, params, user);
      else if (path === 'news')     result = newsRouter(action, body, params, user);
      else if (path === 'reports')  result = reportsRouter(action, body, params, user);
      else if (path === 'init')     result = initSheets();
      else result = error('Маршрут не знайдено');
    }

    return jsonResp(result);
  } catch(err) {
    return jsonResp(error('Помилка сервера: ' + err.message, 500));
  }
}

// ============================================================
//  AUTH
// ============================================================
function login(body) {
  if (!body.email || !body.password) return error('Введіть email і пароль');
  const users = getAll('users');
  const user  = users.find(u => u.email === body.email && u.is_active !== false && u.is_active !== 'false');
  if (!user || !checkPassword(body.password, user.password_hash)) {
    return error('Невірний email або пароль', 401);
  }
  // Оновити last_login
  updateRow('users', user.id, { last_login: new Date().toISOString() });

  const token = createToken({
    user_id:      user.id,
    resident_id:  user.resident_id || '',
    apartment_id: user.apartment_id || '',
    role:         user.role,
    email:        user.email,
    name:         user.full_name,
  });
  return ok({ token, role: user.role, name: user.full_name, email: user.email });
}

function register(body) {
  const { email, password, full_name, apartment_id, phone } = body;
  if (!email || !password || !full_name || !apartment_id) return error('Заповніть всі поля');
  if (password.length < 6) return error('Пароль мінімум 6 символів');

  const users = getAll('users');
  if (users.find(u => u.email === email)) return error('Користувач з таким email вже існує');

  // Перевірка квартири
  const apt = getAll('apartments').find(a => String(a.id) === String(apartment_id));
  if (!apt) return error('Квартиру не знайдено');

  const residentId = genId();
  const now = new Date().toISOString();

  // Додати мешканця
  appendRow('residents', {
    id: residentId, apartment_id, apartment_number: apt.number,
    full_name, phone: phone || '', email, is_owner: true,
    is_active: true, notes: '', created_at: now,
  });

  // Додати користувача
  const userId = genId();
  appendRow('users', {
    id: userId, email, password_hash: hashPassword(password),
    role: 'resident', resident_id: residentId, apartment_id,
    full_name, phone: phone || '', is_active: true,
    created_at: now, last_login: '',
  });

  // Оновити лічильник мешканців квартири
  const cnt = Number(apt.residents_cnt || 0) + 1;
  updateRow('apartments', apt.id, { residents_cnt: cnt });

  const token = createToken({
    user_id: userId, resident_id: residentId,
    apartment_id, role: 'resident', email, name: full_name,
  });
  return ok({ token, role: 'resident', name: full_name }, 'Реєстрація успішна', 201);
}

function getMe(params, e) {
  const user = verifyToken(params.token || '');
  if (!user) return error('Не авторизовано', 401);
  const users = getAll('users');
  const u = users.find(x => x.id === user.user_id);
  if (!u) return error('Користувача не знайдено', 404);
  const apt = getAll('apartments').find(a => String(a.id) === String(u.apartment_id));
  return ok({ ...u, password_hash: undefined, apartment: apt || null });
}

// ============================================================
//  RESIDENTS
// ============================================================
function residentsRouter(action, body, params, user) {
  switch(action) {
    case 'list':   return listResidents(params, user);
    case 'create': return createResident(body, user);
    case 'update': return updateResident(params.id, body, user);
    case 'delete': return deleteResident(params.id, user);
    default: return error('Невідома дія');
  }
}

function listResidents(params, user) {
  let residents = getAll('residents').filter(r => r.is_active !== false && r.is_active !== 'false');
  const search  = (params.search || '').toLowerCase();
  if (search) {
    residents = residents.filter(r =>
      String(r.full_name).toLowerCase().includes(search) ||
      String(r.apartment_number).includes(search)
    );
  }
  if (user.role === 'resident') {
    residents = residents.filter(r => String(r.apartment_id) === String(user.apartment_id));
  }
  return ok(residents);
}

function createResident(body, user) {
  if (!['admin','accountant'].includes(user.role)) return error('Доступ заборонено', 403);
  if (!body.full_name || !body.apartment_id) return error('Заповніть обовязкові поля');
  const apt = getAll('apartments').find(a => String(a.id) === String(body.apartment_id));
  const id  = genId();
  appendRow('residents', {
    id, apartment_id: body.apartment_id,
    apartment_number: apt ? apt.number : '',
    full_name: body.full_name, phone: body.phone || '',
    email: body.email || '', is_owner: body.is_owner ?? true,
    is_active: true, notes: body.notes || '',
    created_at: new Date().toISOString(),
  });
  return ok({ id }, 'Мешканця додано', 201);
}

function updateResident(id, body, user) {
  if (!['admin','accountant'].includes(user.role)) return error('Доступ заборонено', 403);
  updateRow('residents', id, body);
  return ok(null, 'Дані оновлено');
}

function deleteResident(id, user) {
  if (user.role !== 'admin') return error('Доступ заборонено', 403);
  updateRow('residents', id, { is_active: false });
  return ok(null, 'Мешканця деактивовано');
}

// ============================================================
//  APARTMENTS
// ============================================================
function apartmentsRouter(action, body, params, user) {
  if (action === 'list') {
    return ok(getAll('apartments'));
  }
  return error('Невідома дія');
}

// ============================================================
//  METERS & CHARGES
// ============================================================
function metersRouter(action, body, params, user) {
  switch(action) {
    case 'list':             return listMeters(params, user);
    case 'submit':           return submitMeter(body, user);
    case 'charges':          return getCharges(params, user);
    case 'generate_charges': return generateCharges(body, user);
    case 'tariffs':          return ok(getAll('tariffs'));
    case 'update_tariff':    return updateTariff(body, user);
    default: return error('Невідома дія');
  }
}

function listMeters(params, user) {
  const aptId = user.role === 'resident' ? user.apartment_id : (params.apartment_id || '');
  let readings = getAll('meter_readings');
  if (aptId) readings = readings.filter(r => String(r.apartment_id) === String(aptId));
  if (params.type) readings = readings.filter(r => r.meter_type === params.type);
  readings.sort((a, b) => new Date(b.reading_date) - new Date(a.reading_date));
  return ok(readings.slice(0, 50));
}

function submitMeter(body, user) {
  const { apartment_id, meter_type, reading_value, reading_date } = body;
  if (!apartment_id || !meter_type || !reading_value || !reading_date) return error('Заповніть всі поля');
  if (user.role === 'resident' && String(apartment_id) !== String(user.apartment_id)) {
    return error('Доступ заборонено', 403);
  }

  // Попередній показник
  const prev = getAll('meter_readings')
    .filter(r => String(r.apartment_id) === String(apartment_id) && r.meter_type === meter_type)
    .sort((a, b) => new Date(b.reading_date) - new Date(a.reading_date))[0];

  const consumption = prev ? Math.round((Number(reading_value) - Number(prev.reading_value)) * 1000) / 1000 : null;
  if (consumption !== null && consumption < 0) return error('Показник не може бути меншим за попередній');

  const apt = getAll('apartments').find(a => String(a.id) === String(apartment_id));
  const id  = genId();
  appendRow('meter_readings', {
    id, apartment_id, apartment_number: apt ? apt.number : '',
    meter_type, reading_value: Number(reading_value),
    consumption, reading_date,
    entered_by: user.name, source: user.role === 'admin' ? 'admin' : 'resident',
  });
  return ok({ id, consumption }, 'Показник внесено', 201);
}

function getCharges(params, user) {
  const aptId = user.role === 'resident' ? user.apartment_id : (params.apartment_id || '');
  const year  = Number(params.year  || new Date().getFullYear());
  const month = Number(params.month || new Date().getMonth() + 1);

  let charges = getAll('charges').filter(c =>
    String(c.apartment_id) === String(aptId) &&
    Number(c.billing_year) === year &&
    Number(c.billing_month) === month
  );
  let payments = getAll('payments').filter(p =>
    String(p.apartment_id) === String(aptId) &&
    Number(p.billing_year) === year &&
    Number(p.billing_month) === month
  );

  const totalCharged = charges.reduce((s, c) => s + Number(c.amount) - Number(c.subsidy || 0), 0);
  const totalPaid    = payments.filter(p => p.status === 'confirmed').reduce((s, p) => s + Number(p.amount), 0);

  return ok({ charges, payments, total_charged: totalCharged, total_paid: totalPaid, balance: totalCharged - totalPaid });
}

function generateCharges(body, user) {
  if (!['admin','accountant'].includes(user.role)) return error('Доступ заборонено', 403);
  const year  = Number(body.year  || new Date().getFullYear());
  const month = Number(body.month || new Date().getMonth() + 1);

  const apartments = getAll('apartments');
  const tariffs    = getAll('tariffs').reduce((acc, t) => ({ ...acc, [t.service_type]: t }), {});
  const readings   = getAll('meter_readings');
  const existing   = getAll('charges');

  let count = 0;
  apartments.forEach(apt => {
    // Перевірити чи вже є нарахування
    const exists = existing.some(c =>
      String(c.apartment_id) === String(apt.id) &&
      Number(c.billing_year) === year && Number(c.billing_month) === month
    );
    if (exists) return;

    // Утримання будинку
    if (tariffs.maintenance) {
      addCharge(apt, year, month, 'maintenance', tariffs.maintenance, Number(apt.area_m2));
    }
    // Вивіз сміття
    if (tariffs.garbage && Number(apt.residents_cnt) > 0) {
      addCharge(apt, year, month, 'garbage', tariffs.garbage, Number(apt.residents_cnt));
    }
    // Ліфт
    if (tariffs.elevator && Number(apt.residents_cnt) > 0 && Number(apt.floor) > 1) {
      addCharge(apt, year, month, 'elevator', tariffs.elevator, Number(apt.residents_cnt));
    }
    // Лічильники
    ['cold_water','hot_water','electricity','gas'].forEach(type => {
      if (!tariffs[type]) return;
      const reading = readings
        .filter(r => String(r.apartment_id) === String(apt.id) && r.meter_type === type)
        .filter(r => {
          const d = new Date(r.reading_date);
          return d.getFullYear() === year && d.getMonth() + 1 === month;
        })
        .sort((a, b) => new Date(b.reading_date) - new Date(a.reading_date))[0];

      if (reading && Number(reading.consumption) > 0) {
        addCharge(apt, year, month, type, tariffs[type], Number(reading.consumption));
      }
    });
    count++;
  });
  return ok({ apartments_processed: count }, `Нарахування за ${month}/${year} згенеровано`);
}

function addCharge(apt, year, month, service, tariff, volume) {
  appendRow('charges', {
    id: genId(), apartment_id: apt.id, apartment_number: apt.number,
    billing_year: year, billing_month: month, service_type: service,
    tariff_value: Number(tariff.tariff_value), volume,
    amount: Math.round(Number(tariff.tariff_value) * volume * 100) / 100,
    subsidy: 0,
  });
}

function updateTariff(body, user) {
  if (user.role !== 'admin') return error('Доступ заборонено', 403);
  const tariffs = getAll('tariffs');
  const existing = tariffs.find(t => t.service_type === body.service_type);
  if (existing) {
    updateRow('tariffs', existing.id, { tariff_value: body.tariff_value, valid_from: body.valid_from });
  } else {
    appendRow('tariffs', { id: genId(), ...body });
  }
  return ok(null, 'Тариф оновлено');
}

// ============================================================
//  PAYMENTS
// ============================================================
function paymentsRouter(action, body, params, user) {
  switch(action) {
    case 'list':    return listPayments(params, user);
    case 'create':  return createPayment(body, user);
    case 'confirm': return confirmPayment(params.id, user);
    case 'reject':  return rejectPayment(params.id, body, user);
    case 'summary': return getPaymentSummary(params, user);
    default: return error('Невідома дія');
  }
}

function listPayments(params, user) {
  let payments = getAll('payments');
  if (user.role === 'resident') {
    payments = payments.filter(p => String(p.apartment_id) === String(user.apartment_id));
  } else if (params.apartment_id) {
    payments = payments.filter(p => String(p.apartment_id) === String(params.apartment_id));
  }
  if (params.year)  payments = payments.filter(p => Number(p.billing_year) === Number(params.year));
  if (params.month) payments = payments.filter(p => Number(p.billing_month) === Number(params.month));
  payments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  return ok(payments);
}

function createPayment(body, user) {
  const aptId = user.role === 'resident' ? user.apartment_id : (body.apartment_id || user.apartment_id);
  if (!aptId) return error('Не вказано квартиру');
  if (!body.amount || !body.billing_year || !body.billing_month) return error('Заповніть всі поля');

  const apt = getAll('apartments').find(a => String(a.id) === String(aptId));
  const id  = genId();
  appendRow('payments', {
    id, apartment_id: aptId, apartment_number: apt ? apt.number : '',
    resident_name: user.name,
    billing_year: body.billing_year, billing_month: body.billing_month,
    amount: Number(body.amount),
    payment_method: body.payment_method || 'online',
    status: 'pending',
    transaction_id: body.transaction_id || '',
    notes: body.notes || '',
    created_at: new Date().toISOString(),
    confirmed_by: '', confirmed_at: '',
  });
  return ok({ id }, 'Оплату подано, очікує підтвердження', 201);
}

function confirmPayment(id, user) {
  if (!['admin','accountant'].includes(user.role)) return error('Доступ заборонено', 403);
  const pay = getAll('payments').find(p => String(p.id) === String(id));
  if (!pay) return error('Оплату не знайдено', 404);
  if (pay.status !== 'pending') return error('Оплата вже оброблена');
  updateRow('payments', id, { status: 'confirmed', confirmed_by: user.name, confirmed_at: new Date().toISOString() });
  return ok(null, 'Оплату підтверджено');
}

function rejectPayment(id, body, user) {
  if (!['admin','accountant'].includes(user.role)) return error('Доступ заборонено', 403);
  updateRow('payments', id, { status: 'rejected', notes: body.reason || '', confirmed_by: user.name });
  return ok(null, 'Оплату відхилено');
}

function getPaymentSummary(params, user) {
  const year  = Number(params.year  || new Date().getFullYear());
  const month = Number(params.month || new Date().getMonth() + 1);

  if (user.role === 'resident') {
    const aptId   = user.apartment_id;
    const charges = getAll('charges').filter(c =>
      String(c.apartment_id) === String(aptId) && Number(c.billing_year) === year && Number(c.billing_month) === month
    );
    const payments = getAll('payments').filter(p =>
      String(p.apartment_id) === String(aptId) && Number(p.billing_year) === year && Number(p.billing_month) === month && p.status === 'confirmed'
    );
    const charged = charges.reduce((s,c) => s + Number(c.amount), 0);
    const paid    = payments.reduce((s,p) => s + Number(p.amount), 0);
    const requests = getAll('requests').filter(r => String(r.apartment_id) === String(aptId) && !['done','rejected'].includes(r.status));
    return ok({ charged, paid, balance: charged - paid, open_requests: requests.length, month, year });
  }

  // Адмін — загальна статистика
  const allCharges  = getAll('charges').filter(c => Number(c.billing_year) === year && Number(c.billing_month) === month);
  const allPayments = getAll('payments').filter(p => Number(p.billing_year) === year && Number(p.billing_month) === month);
  const confirmed   = allPayments.filter(p => p.status === 'confirmed');
  const pending     = allPayments.filter(p => p.status === 'pending');
  const allResidents = getAll('residents').filter(r => r.is_active !== false && r.is_active !== 'false');
  const openRequests = getAll('requests').filter(r => !['done','rejected'].includes(r.status));

  const totalCharged = allCharges.reduce((s,c) => s + Number(c.amount), 0);
  const totalPaid    = confirmed.reduce((s,p) => s + Number(p.amount), 0);

  return ok({
    total_charged:    totalCharged,
    total_paid:       totalPaid,
    total_debt:       totalCharged - totalPaid,
    pending_payments: pending.length,
    open_requests:    openRequests.length,
    total_residents:  allResidents.length,
    month, year,
  });
}

// ============================================================
//  REQUESTS
// ============================================================
function requestsRouter(action, body, params, user) {
  switch(action) {
    case 'list':   return listRequests(params, user);
    case 'create': return createRequest(body, user);
    case 'update': return updateRequest(params.id, body, user);
    default: return error('Невідома дія');
  }
}

function listRequests(params, user) {
  let reqs = getAll('requests');
  if (user.role === 'resident') reqs = reqs.filter(r => String(r.apartment_id) === String(user.apartment_id));
  if (params.status) reqs = reqs.filter(r => r.status === params.status);
  reqs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  return ok(reqs);
}

function createRequest(body, user) {
  if (!body.category || !body.title) return error('Заповніть обовязкові поля');
  const apt = getAll('apartments').find(a => String(a.id) === String(user.apartment_id));
  const id  = genId();
  const now = new Date().toISOString();
  appendRow('requests', {
    id, apartment_id: user.apartment_id || '',
    apartment_number: apt ? apt.number : '',
    user_id: user.user_id,
    category: body.category, title: body.title,
    description: body.description || '',
    priority: body.priority || 'medium',
    status: 'new', assigned_to: '', resolution: '',
    created_at: now, updated_at: now,
  });
  return ok({ id }, 'Заявку подано', 201);
}

function updateRequest(id, body, user) {
  if (!['admin'].includes(user.role)) return error('Доступ заборонено', 403);
  updateRow('requests', id, { ...body, updated_at: new Date().toISOString() });
  return ok(null, 'Заявку оновлено');
}

// ============================================================
//  NEWS
// ============================================================
function newsRouter(action, body, params, user) {
  switch(action) {
    case 'list':   return listNews();
    case 'create': return createNews(body, user);
    case 'delete': return deleteNews(params.id, user);
    default: return error('Невідома дія');
  }
}

function listNews() {
  const news = getAll('news')
    .filter(n => n.is_published !== false && n.is_published !== 'false')
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  return ok(news);
}

function createNews(body, user) {
  if (user.role !== 'admin') return error('Доступ заборонено', 403);
  if (!body.title || !body.body) return error('Заповніть всі поля');
  const id = genId();
  appendRow('news', {
    id, author_id: user.user_id, author_name: user.name,
    title: body.title, body: body.body,
    type: body.type || 'info', is_published: true,
    created_at: new Date().toISOString(),
  });
  return ok({ id }, 'Новину опубліковано', 201);
}

function deleteNews(id, user) {
  if (user.role !== 'admin') return error('Доступ заборонено', 403);
  updateRow('news', id, { is_published: false });
  return ok(null, 'Новину приховано');
}

// ============================================================
//  REPORTS
// ============================================================
function reportsRouter(action, body, params, user) {
  if (!['admin','accountant'].includes(user.role)) return error('Доступ заборонено', 403);
  switch(action) {
    case 'debtors':         return getDebtors();
    case 'monthly_summary': return getMonthlySummary(params);
    case 'consumption':     return getConsumption(params);
    case 'requests_stats':  return getRequestsStats();
    default: return error('Невідома дія');
  }
}

function getDebtors() {
  const apartments = getAll('apartments');
  const charges    = getAll('charges');
  const payments   = getAll('payments').filter(p => p.status === 'confirmed');
  const residents  = getAll('residents').filter(r => r.is_active !== false && r.is_active !== 'false' && r.is_owner !== false);

  const debtors = apartments.map(apt => {
    const charged = charges.filter(c => String(c.apartment_id) === String(apt.id)).reduce((s,c) => s + Number(c.amount), 0);
    const paid    = payments.filter(p => String(p.apartment_id) === String(apt.id)).reduce((s,p) => s + Number(p.amount), 0);
    const debt    = charged - paid;
    const owner   = residents.find(r => String(r.apartment_id) === String(apt.id));
    return { apartment_number: apt.number, floor: apt.floor, full_name: owner ? owner.full_name : '', phone: owner ? owner.phone : '', total_charged: charged, total_paid: paid, debt };
  }).filter(d => d.debt > 0).sort((a, b) => b.debt - a.debt);

  return ok({ debtors, count: debtors.length, total_debt: debtors.reduce((s,d) => s + d.debt, 0) });
}

function getMonthlySummary(params) {
  const months = Number(params.months || 6);
  const charges  = getAll('charges');
  const payments = getAll('payments').filter(p => p.status === 'confirmed');

  const grouped = {};
  charges.forEach(c => {
    const key = `${c.billing_year}-${String(c.billing_month).padStart(2,'0')}`;
    if (!grouped[key]) grouped[key] = { year: c.billing_year, month: c.billing_month, charged: 0, paid: 0 };
    grouped[key].charged += Number(c.amount);
  });
  payments.forEach(p => {
    const key = `${p.billing_year}-${String(p.billing_month).padStart(2,'0')}`;
    if (!grouped[key]) grouped[key] = { year: p.billing_year, month: p.billing_month, charged: 0, paid: 0 };
    grouped[key].paid += Number(p.amount);
  });

  const result = Object.values(grouped)
    .map(g => ({ ...g, debt: g.charged - g.paid }))
    .sort((a, b) => b.year - a.year || b.month - a.month)
    .slice(0, months);

  return ok(result);
}

function getConsumption(params) {
  const year = Number(params.year || new Date().getFullYear());
  const readings = getAll('meter_readings').filter(r => new Date(r.reading_date).getFullYear() === year);
  const byType = {};
  readings.forEach(r => {
    if (!byType[r.meter_type]) byType[r.meter_type] = [];
    byType[r.meter_type].push({ month: new Date(r.reading_date).getMonth() + 1, consumption: Number(r.consumption || 0) });
  });
  return ok({ by_service: byType, year });
}

function getRequestsStats() {
  const reqs = getAll('requests');
  const byStatus   = groupCount(reqs, 'status');
  const byCategory = groupCount(reqs, 'category');
  const byPriority = groupCount(reqs, 'priority');
  return ok({ by_status: byStatus, by_category: byCategory, by_priority: byPriority, total: reqs.length });
}

function groupCount(arr, key) {
  return Object.entries(arr.reduce((acc, item) => {
    acc[item[key]] = (acc[item[key]] || 0) + 1;
    return acc;
  }, {})).map(([k, v]) => ({ [key]: k, count: v }));
}

// ============================================================
//  SHEETS HELPERS
// ============================================================
function getSheet(name) {
  return SpreadsheetApp.openById(SPREADSHEET_ID).getSheetByName(name);
}

function getAll(sheetName) {
  const sheet = getSheet(sheetName);
  if (!sheet) return [];
  const data = sheet.getDataRange().getValues();
  if (data.length < 2) return [];
  const headers = data[0];
  return data.slice(1).map(row => {
    const obj = {};
    headers.forEach((h, i) => { obj[h] = row[i]; });
    return obj;
  });
}

function appendRow(sheetName, obj) {
  const sheet = getSheet(sheetName);
  const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
  const row = headers.map(h => obj[h] !== undefined ? obj[h] : '');
  sheet.appendRow(row);
}

function updateRow(sheetName, id, updates) {
  const sheet   = getSheet(sheetName);
  const data    = sheet.getDataRange().getValues();
  const headers = data[0];
  const idIdx   = headers.indexOf('id');
  if (idIdx < 0) return;

  for (let i = 1; i < data.length; i++) {
    if (String(data[i][idIdx]) === String(id)) {
      Object.entries(updates).forEach(([key, val]) => {
        const col = headers.indexOf(key);
        if (col >= 0) sheet.getRange(i + 1, col + 1).setValue(val);
      });
      break;
    }
  }
}

// ============================================================
//  AUTH HELPERS (JWT спрощений)
// ============================================================
function createToken(payload) {
  const header  = Utilities.base64EncodeWebSafe(JSON.stringify({ alg:'HS256', typ:'JWT' }));
  payload.exp   = Math.floor(Date.now() / 1000) + 86400; // 24 год
  const body    = Utilities.base64EncodeWebSafe(JSON.stringify(payload));
  const sig     = Utilities.base64EncodeWebSafe(
    Utilities.computeHmacSha256Signature(`${header}.${body}`, JWT_SECRET)
  );
  return `${header}.${body}.${sig}`;
}

function verifyToken(token) {
  try {
    if (!token) return null;
    const [header, body, sig] = token.split('.');
    const expected = Utilities.base64EncodeWebSafe(
      Utilities.computeHmacSha256Signature(`${header}.${body}`, JWT_SECRET)
    );
    if (sig !== expected) return null;
    const payload = JSON.parse(Utilities.newBlob(Utilities.base64DecodeWebSafe(body)).getDataAsString());
    if (payload.exp < Math.floor(Date.now() / 1000)) return null;
    return payload;
  } catch(e) { return null; }
}

function hashPassword(password) {
  const bytes = Utilities.computeHmacSha256Signature(password, JWT_SECRET);
  return Utilities.base64EncodeWebSafe(bytes);
}

function checkPassword(password, hash) {
  return hashPassword(password) === hash;
}

// ============================================================
//  RESPONSE HELPERS
// ============================================================
function ok(data, message = 'OK', code = 200) {
  return { success: true, message, data, code };
}
function error(message, code = 400) {
  return { success: false, message, data: null, code };
}
function jsonResp(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
function genId() {
  return `${Date.now()}_${Math.random().toString(36).substr(2,6)}`;
}
