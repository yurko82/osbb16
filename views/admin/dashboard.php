<div class="space-y-8">
    
    <!-- Admin Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-bold mb-2">
                <span>🛡 Панель управління ОСББ</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Загальний огляд будинку</h1>
            <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($osbb['name'] ?? 'ОСББ') ?> (<?= htmlspecialchars($osbb['address'] ?? '') ?>)</p>
        </div>

        <!-- Monthly Bill Generator Button Modal Trigger -->
        <button onclick="document.getElementById('generate-bills-modal').classList.remove('hidden')"
                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/20 transition flex items-center space-x-2 self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Згенерувати нарахування</span>
        </button>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Всього квартир</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2"><?= $totalApts ?></div>
            <span class="text-xs text-slate-400 mt-1 block">2 під’їзди</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Зареєстровано мешканців</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2"><?= $totalResidents ?></div>
            <span class="text-xs text-emerald-600 mt-1 font-semibold block">активні облікові записи</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Загальний борг по будинку</span>
            <div class="text-3xl font-extrabold text-red-600 mt-2"><?= number_format($totalDebt, 2) ?> <span class="text-sm text-slate-400">грн</span></div>
            <span class="text-xs text-slate-400 mt-1 block">неоплачені рахунки</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Відкритих заявок</span>
            <div class="text-3xl font-extrabold text-amber-500 mt-2"><?= $openRequests ?></div>
            <a href="/admin/requests" class="text-xs text-purple-600 font-bold hover:underline mt-1 block">Диспетчерська →</a>
        </div>
    </div>

    <!-- Navigation Pills for Admin sections -->
    <div class="flex items-center space-x-2 border-b border-slate-200 pb-4">
        <a href="/admin" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-600 text-white shadow">Квартири та баланси</a>
        <a href="/admin/requests" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">Заявки (<?= $openRequests ?>)</a>
        <a href="/admin/news" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">Оголошення & Розсилка</a>
        <a href="/admin/tariffs" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">Тарифи</a>
    </div>

    <!-- Apartments & Debts Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Реєстр квартир та стан розрахунків</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead>
                    <tr class="text-left font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Кв. №</th>
                        <th class="py-3 px-4">Під'їзд / Поверх</th>
                        <th class="py-3 px-4">Площа</th>
                        <th class="py-3 px-4">Особовий рахунок</th>
                        <th class="py-3 px-4">Мешканці</th>
                        <th class="py-3 px-4 text-right">Поточний борг</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($apartments as $apt): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">№ <?= $apt['number'] ?></td>
                            <td class="py-3.5 px-4 font-semibold text-slate-500">Під’їзд <?= $apt['entrance'] ?>, пов. <?= $apt['floor'] ?></td>
                            <td class="py-3.5 px-4 font-medium"><?= number_format((float)$apt['area_m2'], 1) ?> м²</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700"><?= htmlspecialchars($apt['account_number']) ?></td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                <?= htmlspecialchars($apt['residents_names'] ?: 'не зареєстровано') ?>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold <?= (float)$apt['current_debt'] > 0 ? 'text-red-600' : 'text-emerald-600' ?>">
                                <?= number_format((float)$apt['current_debt'], 2) ?> грн
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Monthly Bill Generation -->
    <div id="generate-bills-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Генерація рахунків</h3>
                <button onclick="document.getElementById('generate-bills-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <p class="text-xs text-slate-500 mt-3">
                Система автоматично розрахує вартість утримання будинку (за площею квартир) та додасть споживання лічильників за вказаний період.
            </p>

            <form method="POST" action="/admin/bills/generate" class="mt-4 space-y-4">
                <?= $csrf ?>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Місяць</label>
                        <select name="month" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= (int)date('n') === $m ? 'selected' : '' ?>>
                                    <?= sprintf('%02d', $m) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Рік</label>
                        <select name="year" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold">
                            <option value="2026" selected>2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('generate-bills-modal').classList.add('hidden')"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Скасувати
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md transition">
                        Сформувати рахунки
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
