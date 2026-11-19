<div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-2xl shadow-2xl p-8 sm:p-10">
    <div class="text-center mb-8">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-white">МійДім ОСББ</h1>
        <p class="text-sm text-slate-400 mt-1">Вхід до особистого кабінету</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center space-x-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login" class="space-y-5">
        <?= $csrf ?>

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Електронна пошта</label>
            <input type="email" id="email" name="email" required placeholder="ivan@test.ua"
                   class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition text-sm">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Пароль</label>
            <input type="password" id="password" name="password" required placeholder="••••••••"
                   class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition text-sm">
        </div>

        <button type="submit"
                class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-brand-500/25 transition duration-150 transform active:scale-[0.99]">
            Увійти в кабінет
        </button>
    </form>

    <!-- Quick Test Accounts -->
    <div class="mt-8 pt-6 border-t border-slate-700/60">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3 text-center">Тестові акаунти (клікніть для заповнення):</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <button type="button" onclick="fillCreds('admin@mydom.ua', 'admin123')"
                    class="p-2.5 rounded-lg bg-slate-900/50 hover:bg-slate-700 border border-slate-700/60 text-left transition">
                <div class="font-bold text-indigo-400">Адміністратор</div>
                <div class="text-slate-400 truncate">admin@mydom.ua</div>
            </button>
            <button type="button" onclick="fillCreds('ivan@test.ua', '123456')"
                    class="p-2.5 rounded-lg bg-slate-900/50 hover:bg-slate-700 border border-slate-700/60 text-left transition">
                <div class="font-bold text-emerald-400">Мешканець (кв. 1)</div>
                <div class="text-slate-400 truncate">ivan@test.ua</div>
            </button>
            <button type="button" onclick="fillCreds('accountant@mydom.ua', '123456')"
                    class="p-2.5 rounded-lg bg-slate-900/50 hover:bg-slate-700 border border-slate-700/60 text-left transition">
                <div class="font-bold text-amber-400">Бухгалтер</div>
                <div class="text-slate-400 truncate">accountant@mydom.ua</div>
            </button>
            <button type="button" onclick="fillCreds('master@mydom.ua', '123456')"
                    class="p-2.5 rounded-lg bg-slate-900/50 hover:bg-slate-700 border border-slate-700/60 text-left transition">
                <div class="font-bold text-sky-400">Майстер</div>
                <div class="text-slate-400 truncate">master@mydom.ua</div>
            </button>
        </div>
    </div>
</div>

<script>
function fillCreds(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
}
</script>
