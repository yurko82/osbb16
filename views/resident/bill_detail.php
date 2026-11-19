<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <a href="/bills" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Повернутися до списку рахунків</span>
        </a>

        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5 print:hidden">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Роздрукувати квитанцію</span>
        </button>
    </div>

    <!-- Bill Paper Container -->
    <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200/80 shadow-sm space-y-8">
        
        <!-- Header: OSBB & Account info -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-slate-200">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-brand-600">Рахунок на оплату ЖКП</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= htmlspecialchars($bill['bill_number']) ?></h1>
                <p class="text-xs text-slate-500 mt-1">Період: <b><?= sprintf('%02d/%04d', $bill['billing_month'], $bill['billing_year']) ?></b> | Сформовано: <?= date('d.m.Y', strtotime($bill['generated_at'])) ?></p>
            </div>

            <div class="text-left sm:text-right text-xs text-slate-600 space-y-1">
                <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($osbb['name'] ?? 'ОСББ') ?></div>
                <div>ЄДРПОУ: <b><?= htmlspecialchars($osbb['edrpou'] ?? '') ?></b></div>
                <div>IBAN: <b class="font-mono"><?= htmlspecialchars($osbb['iban'] ?? '') ?></b></div>
                <div>Банк: <?= htmlspecialchars($osbb['bank'] ?? '') ?></div>
            </div>
        </div>

        <!-- Resident and Apartment Data -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase block mb-1">Платник:</span>
                <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($user['full_name'] ?? '') ?></div>
                <div class="text-slate-600 mt-0.5">Квартира №<?= htmlspecialchars((string)($apartment['number'] ?? '—')) ?>, О/Р: <span class="font-mono font-bold"><?= htmlspecialchars($apartment['account_number'] ?? '—') ?></span></div>
            </div>
            <div class="sm:text-right">
                <span class="text-slate-400 font-bold uppercase block mb-1">Характеристики:</span>
                <div class="text-slate-700">Опалювальна площа: <b><?= htmlspecialchars((string)($apartment['area_m2'] ?? 0)) ?> м²</b></div>
                <div class="text-slate-700">Кількість мешканців: <b><?= htmlspecialchars((string)($apartment['residents_count'] ?? 1)) ?></b></div>
            </div>
        </div>

        <!-- Items Breakdown Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead>
                    <tr class="text-left font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-2">Послуга</th>
                        <th class="py-3 px-2">Тариф</th>
                        <th class="py-3 px-2">Обсяг / Площа</th>
                        <th class="py-3 px-2 text-right">Сума (грн)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="py-3 px-2 font-bold text-slate-900"><?= htmlspecialchars($item['service_name']) ?></td>
                            <td class="py-3 px-2"><?= number_format((float)$item['tariff_rate'], 2) ?> грн/<?= htmlspecialchars($item['unit']) ?></td>
                            <td class="py-3 px-2"><?= number_format((float)$item['quantity'], 3) ?> <?= htmlspecialchars($item['unit']) ?></td>
                            <td class="py-3 px-2 font-mono font-bold text-right text-slate-900"><?= number_format((float)$item['total_amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals & Debt Summary -->
        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row justify-between items-center gap-6">
            <div class="space-y-1 text-xs text-slate-600">
                <div>Нараховано за поточний місяць: <b><?= number_format((float)$bill['maintenance_amount'] + (float)$bill['meters_amount'], 2) ?> грн</b></div>
                <?php if ((float)$bill['previous_debt'] > 0): ?>
                    <div class="text-amber-700 font-semibold">Попередній борг: +<?= number_format((float)$bill['previous_debt'], 2) ?> грн</div>
                <?php endif; ?>
                <div>Вже сплачено: <span class="text-emerald-700 font-bold"><?= number_format((float)$bill['paid_amount'], 2) ?> грн</span></div>
            </div>

            <div class="text-right">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Всього до сплати:</span>
                <div class="text-3xl font-extrabold text-slate-900">
                    <?= number_format(max(0.0, (float)$bill['total_amount'] - (float)$bill['paid_amount']), 2) ?> <span class="text-base font-semibold text-slate-500">грн</span>
                </div>
            </div>
        </div>

        <!-- Official NBU QR Code Section -->
        <div class="p-6 rounded-3xl bg-brand-50/50 border border-brand-100 flex flex-col sm:flex-row items-center gap-6">
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-200 flex-shrink-0 text-center">
                <img src="<?= htmlspecialchars($qrData['qr_url']) ?>" alt="QR-код НБУ" class="w-40 h-40 mx-auto rounded-lg">
                <span class="text-[10px] text-slate-400 font-bold uppercase mt-1 block">Стандарт НБУ</span>
            </div>

            <div class="flex-1 space-y-2 text-xs">
                <h3 class="text-sm font-bold text-slate-900">Як швидко оплатити квитанцію:</h3>
                <ol class="list-decimal list-inside text-slate-600 space-y-1">
                    <li>Відкрийте банківський додаток (<b>Приват24, Monobank, Ощад 24/7</b>).</li>
                    <li>Увімкніть сканер QR-кодів та наведіть камеру на код зліва.</li>
                    <li>Перевірте реквізити та підтвердіть платіж.</li>
                </ol>

                <div class="pt-2 font-mono text-[11px] text-slate-500 space-y-0.5">
                    <div><b>IBAN:</b> <?= htmlspecialchars($qrData['iban']) ?></div>
                    <div><b>Призначення:</b> <?= htmlspecialchars($qrData['purpose']) ?></div>
                </div>
            </div>
        </div>

    </div>
</div>
