<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Управління тарифами</h1>
            <p class="text-xs text-slate-500 mt-1">Тарифи використовуються при щомісячному розрахунку квитанцій</p>
        </div>

        <a href="/admin" class="text-xs font-bold text-slate-500 hover:text-slate-800">← Повернутися до огляду</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <div class="space-y-4">
            <?php foreach ($tariffs as $t): ?>
                <form method="POST" action="/admin/tariffs/update" class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <?= $csrf ?>
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">

                    <div class="space-y-0.5">
                        <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($t['service_name']) ?></div>
                        <div class="text-xs text-slate-500"><?= htmlspecialchars($t['description'] ?? '') ?></div>
                        <div class="text-[10px] text-slate-400 font-mono">Код: <?= htmlspecialchars($t['service_code']) ?></div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="flex items-center space-x-1">
                            <input type="number" step="0.01" name="rate" value="<?= htmlspecialchars((string)$t['rate']) ?>" required
                                   class="w-28 px-3 py-2 rounded-xl border border-slate-200 text-slate-900 text-xs font-bold text-right focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <span class="text-xs font-semibold text-slate-500">грн/<?= htmlspecialchars($t['unit']) ?></span>
                        </div>

                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition">
                            Зберегти
                        </button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
