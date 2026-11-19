<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Заявки та звернення</h1>
            <p class="text-xs text-slate-500 mt-1">Виклик майстра, повідомлення про несправності (Квартира №<?= htmlspecialchars((string)($apartment['number'] ?? '—')) ?>)</p>
        </div>

        <button onclick="document.getElementById('new-request-modal').classList.remove('hidden')"
                class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md transition flex items-center space-x-1.5 self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Нова заявка</span>
        </button>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Requests List -->
    <div class="space-y-4">
        <?php if (empty($requests)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Заявок ще немає</h3>
                <p class="text-xs text-slate-500 mt-1">Ви можете створити звернення, якщо виникла несправність у квартирі або під'їзді.</p>
            </div>
        <?php else: ?>
            <?php foreach ($requests as $r): ?>
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase 
                                <?= $r['priority'] === 'urgent' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $r['priority'] === 'urgent' ? 'Терміново' : 'Звичайний' ?>
                            </span>
                            <span class="text-xs text-slate-400 font-semibold">•</span>
                            <span class="text-xs text-slate-400"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
                        </div>
                        <h2 class="text-base font-bold text-slate-900"><?= htmlspecialchars($r['title']) ?></h2>
                        <p class="text-xs text-slate-600"><?= htmlspecialchars($r['description']) ?></p>

                        <?php if (!empty($r['resolution'])): ?>
                            <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                <span class="font-bold text-slate-700">Відповідь майстра:</span>
                                <span class="text-slate-600 ml-1"><?= htmlspecialchars($r['resolution']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="sm:text-right flex-shrink-0">
                        <?php if ($r['status'] === 'done'): ?>
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 inline-block">Виконано</span>
                        <?php elseif ($r['status'] === 'in_progress'): ?>
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 text-brand-700 inline-block">В роботі</span>
                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 inline-block">Очікує розгляду</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($r['master_name'])): ?>
                            <div class="text-[11px] text-slate-400 mt-1">Майстер: <?= htmlspecialchars($r['master_name']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Modal: New Request -->
    <div id="new-request-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Створити нову заявку</h3>
                <button onclick="document.getElementById('new-request-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="/requests" class="mt-4 space-y-4">
                <?= $csrf ?>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Категорія несправності</label>
                    <select name="category" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800">
                        <option value="plumbing">Сантехніка (крани, стояки, протікання)</option>
                        <option value="electrical">Електрика (освітлення, щитки)</option>
                        <option value="intercom">Домофон / Ворота / Шлагбаум</option>
                        <option value="heating">Опалення / Батареї</option>
                        <option value="cleaning">Прибирання території / під’їзду</option>
                        <option value="other">Інше</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Короткий заголовок</label>
                    <input type="text" name="title" required placeholder="Наприклад: Капає кран у підвалі"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Детальний опис проблеми</label>
                    <textarea name="description" rows="3" required placeholder="Опишіть точне місце та характер несправності..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Пріоритет</label>
                    <select name="priority" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800">
                        <option value="normal">Звичайний</option>
                        <option value="urgent">Терміновий (аварійна ситуація)</option>
                        <option value="low">Низький</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('new-request-modal').classList.add('hidden')"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Скасувати
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md transition">
                        Надіслати заявку
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
