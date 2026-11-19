<div class="space-y-4">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-brand-600 to-indigo-700 rounded-2xl p-5 text-white shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-200">Особистий кабінет</span>
                <h1 class="text-xl font-bold mt-0.5">Квартира №<?= htmlspecialchars((string)($apartment['number'] ?? '—')) ?></h1>
                <p class="text-xs text-indigo-100 mt-0.5"><?= htmlspecialchars($user['full_name'] ?? '') ?></p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-indigo-200 uppercase font-bold">Баланс</span>
                <div class="text-lg font-black <?= $debt > 0 ? 'text-amber-300' : 'text-emerald-300' ?>">
                    <?= number_format($debt, 2) ?> грн
                </div>
            </div>
        </div>
    </div>

    <!-- Tab navigation -->
    <div class="flex rounded-xl bg-slate-200/80 p-1 text-xs font-bold" id="webapp-tabs">
        <button type="button" onclick="switchTab('bill')" id="tab-btn-bill" class="flex-1 py-2 rounded-lg bg-white shadow-sm text-slate-900 transition">
            💳 Рахунок & QR
        </button>
        <button type="button" onclick="switchTab('meters')" id="tab-btn-meters" class="flex-1 py-2 rounded-lg text-slate-600 transition">
            🚰 Лічильники
        </button>
        <button type="button" onclick="switchTab('requests')" id="tab-btn-requests" class="flex-1 py-2 rounded-lg text-slate-600 transition">
            🛠 Заявки
        </button>
    </div>

    <!-- Tab Content: Bill & QR -->
    <div id="tab-content-bill" class="space-y-4">
        <?php if ($latestBill): ?>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-xs font-bold text-slate-800">Рахунок за <?= sprintf('%02d/%04d', $latestBill['billing_month'], $latestBill['billing_year']) ?></span>
                        <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($latestBill['bill_number']) ?></div>
                    </div>
                    <?php if ($latestBill['status'] === 'paid'): ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Оплачено</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700">Не сплачено</span>
                    <?php endif; ?>
                </div>

                <?php if ($qrData && $latestBill['status'] !== 'paid'): ?>
                    <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <img src="<?= htmlspecialchars($qrData['qr_url']) ?>" alt="QR" class="w-40 h-40 mx-auto rounded-lg shadow-sm">
                        <div class="text-[10px] text-slate-500 font-semibold mt-2">QR-код за стандартом НБУ для оплати в додатку банку</div>
                    </div>

                    <div class="text-[11px] text-slate-600 space-y-1 font-mono bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div><b>Сума:</b> <?= number_format((float)$latestBill['total_amount'], 2) ?> грн</div>
                        <div><b>IBAN:</b> <?= htmlspecialchars($qrData['iban']) ?></div>
                        <div class="truncate"><b>Призначення:</b> <?= htmlspecialchars($qrData['purpose']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl p-6 text-center text-xs text-slate-400">Нарахувань не знайдено.</div>
        <?php endif; ?>
    </div>

    <!-- Tab Content: Meters -->
    <div id="tab-content-meters" class="space-y-3 hidden">
        <?php foreach ($meters as $m): ?>
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <div class="font-bold text-xs text-slate-900"><?= htmlspecialchars($m['meter_name']) ?></div>
                    <div class="text-[10px] text-slate-400 font-mono">№ <?= htmlspecialchars($m['serial_number']) ?></div>
                    <div class="text-[11px] font-bold text-brand-600 mt-1">
                        Поточний: <?= $m['last_reading'] !== null ? number_format((float)$m['last_reading'], 3) : '0' ?> <?= htmlspecialchars($m['unit']) ?>
                    </div>
                </div>

                <button onclick="promptReading(<?= $m['id'] ?>, '<?= htmlspecialchars($m['meter_name']) ?>')"
                        class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs transition">
                    Внести
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tab Content: Requests -->
    <div id="tab-content-requests" class="space-y-3 hidden">
        <?php if (empty($requests)): ?>
            <div class="bg-white rounded-2xl p-6 text-center text-xs text-slate-400">Заявок немає</div>
        <?php else: ?>
            <?php foreach ($requests as $r): ?>
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-slate-900"><?= htmlspecialchars($r['title']) ?></span>
                        <span class="text-[10px] font-bold uppercase <?= $r['status'] === 'done' ? 'text-emerald-600' : 'text-amber-600' ?>">
                            <?= $r['status'] ?>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500"><?= htmlspecialchars($r['description']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
function switchTab(tab) {
    ['bill', 'meters', 'requests'].forEach(t => {
        document.getElementById('tab-content-' + t).classList.add('hidden');
        document.getElementById('tab-btn-' + t).classList.remove('bg-white', 'shadow-sm', 'text-slate-900');
        document.getElementById('tab-btn-' + t).classList.add('text-slate-600');
    });

    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    document.getElementById('tab-btn-' + tab).classList.add('bg-white', 'shadow-sm', 'text-slate-900');
    document.getElementById('tab-btn-' + tab).classList.remove('text-slate-600');
}

function promptReading(meterId, meterName) {
    const val = prompt('Введіть новий показник для: ' + meterName);
    if (val) {
        fetch('/api/meters/reading', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ apartment_meter_id: meterId, reading_value: parseFloat(val), source: 'telegram_webapp' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✅ Показник збережено!');
                location.reload();
            } else {
                alert('Помилка: ' + (data.error || 'не вдалося зберегти'));
            }
        });
    }
}
</script>
