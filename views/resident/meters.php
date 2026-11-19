<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Показники лічильників</h1>
            <p class="text-xs text-slate-500 mt-1">Внесення поточних даних та історія споживання (Квартира №<?= htmlspecialchars((string)($apartment['number'] ?? '—')) ?>)</p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Meter Input Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($meters as $m): ?>
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">№ <?= htmlspecialchars($m['serial_number']) ?></span>
                        <span class="text-xs font-semibold text-slate-400"><?= htmlspecialchars($m['unit']) ?></span>
                    </div>

                    <h2 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($m['meter_name']) ?></h2>
                    
                    <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-400 font-semibold uppercase">Останній показник</div>
                        <div class="text-2xl font-extrabold text-slate-800 mt-0.5">
                            <?= $m['last_reading'] !== null ? number_format((float)$m['last_reading'], 3) : '0.000' ?>
                            <span class="text-sm font-normal text-slate-500"><?= htmlspecialchars($m['unit']) ?></span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1">
                            Дата: <?= $m['last_reading_date'] ? date('d.m.Y', strtotime($m['last_reading_date'])) : '—' ?>
                        </div>
                    </div>
                </div>

                <!-- Submission Form -->
                <form method="POST" action="/meters" class="mt-6 space-y-3">
                    <?= $csrf ?>
                    <input type="hidden" name="apartment_meter_id" value="<?= $m['id'] ?>">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Новий показник</label>
                        <input type="number" step="0.001" min="<?= (float)($m['last_reading'] ?? 0) ?>" name="reading_value" required placeholder="0.000"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 text-sm font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition">
                        Зберегти показник
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Журнал поданих показників</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead>
                    <tr class="text-left font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Дата</th>
                        <th class="py-3 px-4">Лічильник</th>
                        <th class="py-3 px-4">Показник</th>
                        <th class="py-3 px-4">Споживання</th>
                        <th class="py-3 px-4">Джерело</th>
                        <th class="py-3 px-4">Подав</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($history)): ?>
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Історія показників відсутня.</td></tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-semibold"><?= date('d.m.Y', strtotime($h['reading_date'])) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($h['meter_name']) ?> <span class="font-normal text-slate-400">(№ <?= htmlspecialchars($h['serial_number']) ?>)</span></td>
                                <td class="py-3 px-4 font-mono font-bold"><?= number_format((float)$h['reading_value'], 3) ?> <?= htmlspecialchars($h['unit']) ?></td>
                                <td class="py-3 px-4 font-bold text-indigo-600">+<?= number_format((float)$h['consumption'], 3) ?> <?= htmlspecialchars($h['unit']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $h['source'] === 'telegram' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= htmlspecialchars($h['source']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($h['submitted_by_name'] ?? 'Мешканець') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
