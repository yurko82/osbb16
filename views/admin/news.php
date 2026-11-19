<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Новини та Оголошення ОСББ</h1>
            <p class="text-xs text-slate-500 mt-1">Публікація на сайті та миттєва розсилка в Telegram</p>
        </div>

        <a href="/admin" class="text-xs font-bold text-slate-500 hover:text-slate-800">← Повернутися до огляду</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Creation Form Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Створити нове оголошення</h2>

        <form method="POST" action="/admin/news" class="space-y-4">
            <?= $csrf ?>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Заголовок</label>
                    <input type="text" name="title" required placeholder="Наприклад: Планове відключення води"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Тип сповіщення</label>
                    <select name="type" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800">
                        <option value="info">Звичайне (інформація)</option>
                        <option value="warning">Попередження (роботи/шум)</option>
                        <option value="emergency">🚨 Термінова аварія!</option>
                        <option value="event">Подія / Збори ОСББ</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Текст оголошення</label>
                <textarea name="body" rows="4" required placeholder="Детальний опис ситуації, терміни відновлення, контакти..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                <div class="space-y-2">
                    <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="broadcast_telegram" value="1" checked class="rounded text-purple-600 focus:ring-purple-500">
                        <span>📲 Розіслати всім мешканцям у Telegram-бот</span>
                    </label>
                    <div>
                        <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-500 cursor-pointer">
                            <input type="checkbox" name="is_pinned" value="1" class="rounded text-purple-600 focus:ring-purple-500">
                            <span>Закріпити нагорі стрічки новин</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20 transition">
                    Опублікувати & Розіслати
                </button>
            </div>
        </form>
    </div>

    <!-- History list -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-900">Історія опублікованих оголошень</h2>
        <?php foreach ($newsList as $n): ?>
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $n['type'] === 'emergency' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-700' ?>">
                            <?= htmlspecialchars($n['type']) ?>
                        </span>
                        <?php if ($n['is_pinned']): ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">📌 Закріплено</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-slate-400"><?= date('d.m.Y H:i', strtotime($n['created_at'])) ?></span>
                </div>
                <h3 class="text-base font-bold text-slate-900"><?= htmlspecialchars($n['title']) ?></h3>
                <p class="text-xs text-slate-600"><?= htmlspecialchars($n['body']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
