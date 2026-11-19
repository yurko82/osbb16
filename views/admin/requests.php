<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Диспетчерська заявок</h1>
            <p class="text-xs text-slate-500 mt-1">Призначення майстрів та контроль виконання заявок мешканців</p>
        </div>

        <a href="/admin" class="text-xs font-bold text-slate-500 hover:text-slate-800">← Повернутися до огляду</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php foreach ($requests as $r): ?>
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
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

                <!-- Update Form -->
                <form method="POST" action="/admin/requests/<?= $r['id'] ?>/status" class="pt-2 flex flex-col md:flex-row items-stretch md:items-center gap-3">
                    <?= $csrf ?>

                    <div class="flex-1">
                        <input type="text" name="resolution" value="<?= htmlspecialchars($r['resolution'] ?? '') ?>" placeholder="Звіт або коментар майстра..."
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800">
                    </div>

                    <div class="w-full md:w-48">
                        <select name="assigned_to" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                            <option value="">-- Призначити майстра --</option>
                            <?php foreach ($masters as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= (int)$r['assigned_to'] === (int)$m['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="w-full md:w-36">
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700">
                            <option value="new" <?= $r['status'] === 'new' ? 'selected' : '' ?>>🟡 Нова</option>
                            <option value="in_progress" <?= $r['status'] === 'in_progress' ? 'selected' : '' ?>>🔵 В роботі</option>
                            <option value="done" <?= $r['status'] === 'done' ? 'selected' : '' ?>>🟢 Виконано</option>
                            <option value="cancelled" <?= $r['status'] === 'cancelled' ? 'selected' : '' ?>>⚪ Скасовано</option>
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition flex-shrink-0">
                        Оновити статус
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
