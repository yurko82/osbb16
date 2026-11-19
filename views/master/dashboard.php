<div class="space-y-8">
    <div>
        <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-2">
            <span>🛠 Кабінет техніка / майстра</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Заявки на обслуговування</h1>
        <p class="text-xs text-slate-500 mt-1">Виконання ремонтних робіт та зворотний зв’язок для мешканців</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php if (empty($requests)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80">
                <h3 class="text-sm font-bold text-slate-800">Активних призначених заявок немає</h3>
                <p class="text-xs text-slate-400 mt-1">Всі заявки виконано або відсутні нові звернення.</p>
            </div>
        <?php else: ?>
            <?php foreach ($requests as $r): ?>
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $r['priority'] === 'urgent' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $r['priority'] === 'urgent' ? 'Терміново' : 'Звичайний' ?>
                            </span>
                            <span class="font-bold text-slate-900 text-xs">Квартира №<?= htmlspecialchars((string)$r['apartment_number']) ?></span>
                            <span class="text-slate-400 text-xs">• <?= htmlspecialchars($r['author_name']) ?> (<?= htmlspecialchars($r['author_phone']) ?>)</span>
                        </div>

                        <span class="text-xs text-slate-400"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
                    </div>

                    <div>
                        <h2 class="text-base font-bold text-slate-900"><?= htmlspecialchars($r['title']) ?></h2>
                        <p class="text-xs text-slate-600 mt-1"><?= htmlspecialchars($r['description']) ?></p>
                    </div>

                    <!-- Action form -->
                    <form method="POST" action="/master/requests/<?= $r['id'] ?>/status" class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <?= $csrf ?>

                        <div class="flex-1">
                            <input type="text" name="resolution" value="<?= htmlspecialchars($r['resolution'] ?? '') ?>" placeholder="Опишіть виконану роботу..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800">
                        </div>

                        <div class="w-full sm:w-40">
                            <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700">
                                <option value="in_progress" <?= $r['status'] === 'in_progress' ? 'selected' : '' ?>>🔵 В процесі</option>
                                <option value="done" <?= $r['status'] === 'done' ? 'selected' : '' ?>>🟢 Виконано</option>
                            </select>
                        </div>

                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition">
                            Зберегти звіт
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
