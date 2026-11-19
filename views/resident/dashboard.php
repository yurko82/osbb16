<div class="space-y-8">

    <!-- Welcome & Apartment Summary Banner -->
    <div class="bg-gradient-to-r from-brand-600 via-indigo-600 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-500/15 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold mb-3">
                    <span>🏢 Квартира №<?= htmlspecialchars((string)($apartment['number'] ?? '—')) ?></span>
                    <span>•</span>
                    <span>Під’їзд <?= htmlspecialchars((string)($apartment['entrance'] ?? 1)) ?></span>
                    <span>•</span>
                    <span>Поверх <?= htmlspecialchars((string)($apartment['floor'] ?? 1)) ?></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Вітаємо, <?= htmlspecialchars(explode(' ', $user['full_name'] ?? '')[0]) ?>!
                </h1>
                <p class="text-indigo-100 text-sm mt-1 max-w-xl">
                    Особовий рахунок: <code class="font-mono bg-black/20 px-2 py-0.5 rounded text-white"><?= htmlspecialchars($apartment['account_number'] ?? '—') ?></code> | Площа: <b><?= htmlspecialchars((string)($apartment['area_m2'] ?? 0)) ?> м²</b>
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="/meters" class="px-4 py-2.5 rounded-xl bg-white text-brand-700 hover:bg-brand-50 text-sm font-bold shadow-md transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Подати показники</span>
                </a>
                <a href="/requests" class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-sm font-semibold backdrop-blur-md transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Створити заявку</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Grid: Bills & QR Payment vs Meters Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Payment & Debt Widget (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-6 border-b border-slate-100">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Поточний баланс</span>
                        <div class="flex items-baseline space-x-3 mt-1">
                            <span class="text-3xl sm:text-4xl font-extrabold <?= $debt > 0 ? 'text-red-600' : 'text-emerald-600' ?>">
                                <?= number_format($debt, 2) ?> <span class="text-lg font-semibold text-slate-500">грн</span>
                            </span>
                            <?php if ($debt > 0): ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700">До сплати</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">Заборгованість відсутня</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($latestBill): ?>
                        <div class="text-right hidden sm:block">
                            <span class="text-xs text-slate-400">Останній рахунок:</span>
                            <div class="font-bold text-slate-700 text-sm"><?= $latestBill['billing_month'] ?>/<?= $latestBill['billing_year'] ?> (<?= $latestBill['bill_number'] ?>)</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- QR Payment Section -->
                <?php if ($qrData && $debt > 0): ?>
                    <div class="mt-6 p-6 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center gap-6">
                        <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-200 flex-shrink-0 text-center">
                            <img src="<?= htmlspecialchars($qrData['qr_url']) ?>" alt="QR для оплати за стандартом НБУ" class="w-36 h-36 mx-auto rounded-lg">
                            <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Стандарт НБУ (IBAN)</span>
                        </div>

                        <div class="flex-1 space-y-2 text-sm">
                            <div class="font-bold text-slate-900 text-base flex items-center space-x-2">
                                <span>Швидка сплата через банківський додаток</span>
                            </div>
                            <p class="text-xs text-slate-500">
                                Відкрийте застосунок вашого банку (<b>Monobank, Приват24, Sense</b> тощо) та відскануйте QR-код. Всі реквізити, сума та призначення заповняться автоматично!
                            </p>
                            
                            <div class="pt-2 text-xs text-slate-600 space-y-1 font-mono">
                                <div>IBAN: <b><?= htmlspecialchars($qrData['iban']) ?></b></div>
                                <div>Призначення: <span class="text-slate-500"><?= htmlspecialchars($qrData['purpose']) ?></span></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mt-6 p-6 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center space-x-4 text-emerald-800">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-bold">Чудово! Всі рахунки сплачено</div>
                            <div class="text-xs text-emerald-700 mt-0.5">Дякуємо за своєчасну оплату комунальних послуг вашого будинку.</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="/bills" class="text-brand-600 hover:text-brand-700 inline-flex items-center space-x-1">
                    <span>Історія всіх квитанцій та нарахувань</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Telegram Connection Card (1 col) -->
        <div class="bg-gradient-to-br from-sky-500 to-blue-600 rounded-3xl p-6 sm:p-8 text-white shadow-lg shadow-sky-500/15 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                </div>
                <h2 class="text-xl font-bold">Бот ОСББ у Telegram</h2>
                <p class="text-sky-100 text-xs mt-2 leading-relaxed">
                    Підключіть Telegram, щоб отримувати аварійні сповіщення, передавати показники в 2 кліки та користуватися кабінетом всередині Telegram WebApp.
                </p>

                <div class="mt-4 p-3 rounded-xl bg-white/10 backdrop-blur-md text-xs">
                    <?php if (!empty($user['telegram_chat_id'])): ?>
                        <div class="flex items-center space-x-2 text-white font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span>Бот підключено</span>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center space-x-2 text-amber-200 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span>Ще не підключено</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6">
                <a href="/telegram" class="block w-full text-center py-3 px-4 rounded-xl bg-white text-sky-700 font-bold text-sm shadow hover:bg-sky-50 transition">
                    Керувати підключенням
                </a>
            </div>
        </div>

    </div>

    <!-- Bottom Row: Meters Overview & Community Polls -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" id="polls">
        
        <!-- Meters Quick View -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-900">Показники лічильників</h2>
                <a href="/meters" class="text-xs font-bold text-brand-600 hover:text-brand-700">Переглянути всі</a>
            </div>

            <div class="space-y-4">
                <?php foreach ($meters as $m): ?>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-brand-600 flex items-center justify-center font-bold text-lg">
                                💧
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($m['meter_name']) ?></div>
                                <div class="text-xs text-slate-500 font-mono">№ <?= htmlspecialchars($m['serial_number']) ?></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-base font-extrabold text-slate-900">
                                <?= $m['last_reading'] !== null ? number_format((float)$m['last_reading'], 2) : '—' ?>
                                <span class="text-xs font-medium text-slate-500"><?= htmlspecialchars($m['unit']) ?></span>
                            </div>
                            <div class="text-[10px] text-slate-400">
                                <?= $m['last_reading_date'] ? date('d.m.Y', strtotime($m['last_reading_date'])) : 'не подано' ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Community Polls -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-6">Опитування мешканців</h2>

            <?php if (empty($polls)): ?>
                <p class="text-sm text-slate-400">Наразі активних опитувань немає.</p>
            <?php else: ?>
                <?php foreach ($polls as $poll): ?>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 mb-4">
                        <h3 class="font-bold text-slate-900 text-sm mb-1"><?= htmlspecialchars($poll['title']) ?></h3>
                        <?php if (!empty($poll['description'])): ?>
                            <p class="text-xs text-slate-500 mb-3"><?= htmlspecialchars($poll['description']) ?></p>
                        <?php endif; ?>

                        <form method="POST" action="/vote" class="space-y-2">
                            <?= $csrf ?>
                            <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">

                            <?php foreach ($poll['options'] as $opt): ?>
                                <?php $isVoted = ((int)$poll['user_voted_option_id'] === (int)$opt['id']); ?>
                                <label class="flex items-center justify-between p-3 rounded-xl bg-white border <?= $isVoted ? 'border-brand-500 ring-1 ring-brand-500 bg-brand-50/20' : 'border-slate-200' ?> cursor-pointer text-xs font-semibold hover:border-slate-300 transition">
                                    <div class="flex items-center space-x-2.5">
                                        <input type="radio" name="poll_option_id" value="<?= $opt['id'] ?>" <?= $isVoted ? 'checked' : '' ?> onchange="this.form.submit()" class="text-brand-600 focus:ring-brand-500">
                                        <span class="text-slate-800"><?= htmlspecialchars($opt['option_text']) ?></span>
                                    </div>
                                    <span class="text-slate-400 font-bold"><?= (int)$opt['votes_count'] ?></span>
                                </label>
                            <?php endforeach; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>
