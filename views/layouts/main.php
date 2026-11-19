<!DOCTYPE html>
<html lang="uk" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'МійДім ОСББ') ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-slate-800">

    <!-- Top Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="/dashboard" class="flex items-center space-x-2.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold text-slate-900 tracking-tight leading-none block">МійДім ОСББ</span>
                            <span class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($osbb['name'] ?? 'Затишний Дім') ?></span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="/dashboard" class="px-3 py-2 rounded-lg text-sm font-semibold <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/dashboard') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        Особистий кабінет
                    </a>
                    <a href="/meters" class="px-3 py-2 rounded-lg text-sm font-semibold <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/meters') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        Лічильники
                    </a>
                    <a href="/bills" class="px-3 py-2 rounded-lg text-sm font-semibold <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/bills') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        Рахунки & QR
                    </a>
                    <a href="/requests" class="px-3 py-2 rounded-lg text-sm font-semibold <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/requests') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        Заявки
                    </a>
                    <a href="/telegram" class="px-3 py-2 rounded-lg text-sm font-semibold flex items-center space-x-1.5 text-sky-600 hover:bg-sky-50">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                        <span>Telegram</span>
                    </a>

                    <?php if (in_array($user['role'] ?? '', ['admin', 'accountant'])): ?>
                        <div class="h-5 w-px bg-slate-200 mx-2"></div>
                        <a href="/admin" class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-purple-50 text-purple-700 hover:bg-purple-100 flex items-center space-x-1">
                            <span>Керування ОСББ</span>
                        </a>
                    <?php endif; ?>

                    <?php if (($user['role'] ?? '') === 'master'): ?>
                        <div class="h-5 w-px bg-slate-200 mx-2"></div>
                        <a href="/master" class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-700 hover:bg-amber-100">
                            Кабінет майстра
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-semibold text-slate-900 leading-tight"><?= htmlspecialchars($user['full_name'] ?? 'Користувач') ?></span>
                        <span class="text-xs text-slate-500 font-medium">
                            <?php if (!empty($user['apartment_number'])): ?>
                                Кв. №<?= htmlspecialchars((string)$user['apartment_number']) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($user['role'] ?? '') ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <a href="/logout" title="Вийти з кабінету" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile sub-navigation bar -->
        <div class="md:hidden flex overflow-x-auto px-4 py-2 bg-slate-50 border-t border-slate-200 space-x-2 text-xs font-semibold no-scrollbar">
            <a href="/dashboard" class="px-3 py-1.5 rounded-md whitespace-nowrap <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/dashboard') ? 'bg-white shadow text-brand-700' : 'text-slate-600' ?>">Кабінет</a>
            <a href="/meters" class="px-3 py-1.5 rounded-md whitespace-nowrap <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/meters') ? 'bg-white shadow text-brand-700' : 'text-slate-600' ?>">Лічильники</a>
            <a href="/bills" class="px-3 py-1.5 rounded-md whitespace-nowrap <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/bills') ? 'bg-white shadow text-brand-700' : 'text-slate-600' ?>">Рахунки</a>
            <a href="/requests" class="px-3 py-1.5 rounded-md whitespace-nowrap <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/requests') ? 'bg-white shadow text-brand-700' : 'text-slate-600' ?>">Заявки</a>
            <a href="/telegram" class="px-3 py-1.5 rounded-md whitespace-nowrap text-sky-700">Telegram</a>
            <?php if (in_array($user['role'] ?? '', ['admin', 'accountant'])): ?>
                <a href="/admin" class="px-3 py-1.5 rounded-md whitespace-nowrap bg-purple-600 text-white">Адмін-панель</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-2">
            <div>
                © <?= date('Y') ?> <?= htmlspecialchars($osbb['name'] ?? 'ОСББ') ?>. Всі права захищено.
            </div>
            <div class="flex items-center space-x-4">
                <span>IBAN: <?= htmlspecialchars($osbb['iban'] ?? '') ?></span>
                <span>ЄДРПОУ: <?= htmlspecialchars($osbb['edrpou'] ?? '') ?></span>
                <span>Тел: <?= htmlspecialchars($osbb['phone'] ?? '') ?></span>
            </div>
        </div>
    </footer>

</body>
</html>
