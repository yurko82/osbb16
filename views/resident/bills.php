<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Рахунки та оплата</h1>
            <p class="text-xs text-slate-500 mt-1">Історія нарахувань за ЖКП для квартири №<?= htmlspecialchars((string)($apartment['number'] ?? '—')) ?></p>
        </div>

        <div class="flex items-center space-x-3 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs text-slate-400 font-bold uppercase">Загальний борг:</span>
            <span class="text-lg font-extrabold <?= $debt > 0 ? 'text-red-600' : 'text-emerald-600' ?>">
                <?= number_format($debt, 2) ?> грн
            </span>
        </div>
    </div>

    <!-- Bills Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead>
                    <tr class="text-left font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Період</th>
                        <th class="py-3 px-4">Номер рахунку</th>
                        <th class="py-3 px-4">Утримання</th>
                        <th class="py-3 px-4">Лічильники</th>
                        <th class="py-3 px-4">До сплати</th>
                        <th class="py-3 px-4">Сплачено</th>
                        <th class="py-3 px-4">Статус</th>
                        <th class="py-3 px-4 text-right">Дія</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($bills)): ?>
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">Нарахувань поки немає.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bills as $b): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <?= sprintf('%02d/%04d', $b['billing_month'], $b['billing_year']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-medium text-slate-600"><?= htmlspecialchars($b['bill_number']) ?></td>
                                <td class="py-3.5 px-4"><?= number_format((float)$b['maintenance_amount'], 2) ?> грн</td>
                                <td class="py-3.5 px-4"><?= number_format((float)$b['meters_amount'], 2) ?> грн</td>
                                <td class="py-3.5 px-4 font-bold text-slate-900"><?= number_format((float)$b['total_amount'], 2) ?> грн</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-semibold"><?= number_format((float)$b['paid_amount'], 2) ?> грн</td>
                                <td class="py-3.5 px-4">
                                    <?php if ($b['status'] === 'paid'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Оплачено</span>
                                    <?php elseif ($b['status'] === 'partial'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Частково</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700">Не сплачено</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="/bills/<?= $b['id'] ?>" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold transition">
                                        <span>Квитанція & QR</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
