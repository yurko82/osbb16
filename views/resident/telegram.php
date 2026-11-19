<div class="max-w-2xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Підключення Telegram-бота</h1>
        <p class="text-xs text-slate-500 mt-1">Отримуйте миттєві сповіщення та керуйте послугами будинку в Telegram</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm space-y-6">
        
        <!-- Current Status -->
        <div class="flex items-center justify-between pb-6 border-b border-slate-100">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Статус прив'язки:</span>
                <div class="flex items-center space-x-2 mt-1">
                    <?php if ($isLinked): ?>
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-bold text-slate-900 text-sm">Бот успішно підключено</span>
                        <?php if ($telegramUsername): ?>
                            <span class="text-xs text-slate-400">(@<?= htmlspecialchars($telegramUsername) ?>)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="font-bold text-slate-900 text-sm">Не підключено</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isLinked): ?>
                <form method="POST" action="/telegram/unlink">
                    <?= $csrf ?>
                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition">
                        Відключити
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (!$isLinked): ?>
            <!-- Connect QR & Link -->
            <div class="flex flex-col sm:flex-row items-center gap-6 p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-200 flex-shrink-0 text-center">
                    <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR для підключення бота" class="w-36 h-36 mx-auto rounded-lg">
                    <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Скануйте з телефону</span>
                </div>

                <div class="space-y-3 text-xs text-slate-600">
                    <h3 class="text-sm font-bold text-slate-900">Як підключити за 10 секунд:</h3>
                    <ol class="list-decimal list-inside space-y-1.5">
                        <li>Відскануйте QR-код камерою смартфона або натисніть кнопку нижче.</li>
                        <li>У Telegram натисніть кнопку <b>«Розпочати» (Start)</b>.</li>
                        <li>Бот автоматично закріпить вашу квартиру за чатом!</li>
                    </ol>

                    <div class="pt-2">
                        <a href="<?= htmlspecialchars($deepLink) ?>" target="_blank"
                           class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs shadow-md shadow-sky-500/20 transition">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                            <span>Відкрити бота у Telegram</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="p-6 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 space-y-2 text-xs">
                <div class="font-bold text-sm">Бот активний і готовий до роботи!</div>
                <p>Тепер ви можете надсилати команди боту:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><b>💰 Мій рахунок та QR</b> — перевірка балансу та генерація квитанції НБУ для оплати</li>
                    <li><b>🚰 Подати показники</b> — швидке передавання показників лічильників</li>
                    <li><b>🛠 Мої заявки</b> — моніторинг статусу виконання ремонтів</li>
                    <li><b>📱 Відкрити кабінет (WebApp)</b> — повноцінний особистий кабінет всередині Telegram</li>
                </ul>
            </div>
        <?php endif; ?>

    </div>
</div>
