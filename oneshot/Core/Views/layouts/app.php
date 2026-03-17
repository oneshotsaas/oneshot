<?= render('Core::layouts/_head', ['layout_section' => 'app']) ?>
<body class="min-h-screen bg-base-100">

<div class="drawer lg:drawer-open">
    <input id="app-drawer" type="checkbox" class="drawer-toggle" />

    <!-- Main content -->
    <div class="drawer-content flex flex-col min-h-screen">

        <!-- Topbar -->
        <header class="sticky top-0 z-30 flex items-center h-14 px-4 bg-base-100 border-b border-base-200 gap-3">
            <label for="app-drawer" class="btn btn-ghost btn-sm btn-square lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </label>

            <div class="flex-1 min-w-0">
                <?= render('Core::layouts/_breadcrumbs') ?>
            </div>

            <?php if (!empty($page_actions_view ?? '') || !empty($page_actions ?? '')): ?>
            <div class="flex items-center gap-2 shrink-0">
                <?= !empty($page_actions_view) ? render($page_actions_view, get_defined_vars()) : $page_actions ?>
            </div>
            <?php endif; ?>
        </header>

        <!-- Sub-bar (filters / tabs) -->
        <?php if (!empty($page_subbar_view ?? '') || !empty($page_subbar ?? '')): ?>
        <div class="sticky top-14 z-20 px-4 py-2 bg-base-100 border-b border-base-200">
            <?= !empty($page_subbar_view) ? render($page_subbar_view, get_defined_vars()) : $page_subbar ?>
        </div>
        <?php endif; ?>

        <?php if (session()->has('success') || session()->has('error') || session()->has('info')): ?>
        <div class="px-6 pt-4">
            <?= render('Core::layouts/_flash') ?>
        </div>
        <?php endif; ?>

        <main class="flex-1 p-6">
            <?= $content ?>
        </main>
    </div>

    <!-- Sidebar -->
    <div class="drawer-side z-40">
        <label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"></label>

        <aside class="w-64 min-h-full bg-base-200 flex flex-col border-r border-base-300">

            <!-- Logo -->
            <div class="h-14 flex items-center px-4 border-b border-base-300 shrink-0">
                <?php $logo = option('branding.logo_url', ''); ?>
                <?php if ($logo): ?>
                <img src="<?= esc($logo) ?>" alt="Logo" class="h-7 w-auto object-contain">
                <?php else: ?>
                <img src="/logo-icon.svg" alt="" class="h-7 w-7">
                <span class="text-lg font-extrabold tracking-tight"><?= esc(option('general.app_name', 'OneShot')) ?></span>
                <?php endif; ?>
            </div>

            <!-- Main nav -->
            <nav class="flex-1 overflow-y-auto py-3">
                <?= render('Core::layouts/_nav', ['navItems' => config('Nav')->app]) ?>
            </nav>

            <!-- Bottom nav -->
            <div class="border-t border-base-300 px-3 py-2 shrink-0 flex items-center gap-2">
                <div class="bg-primary text-primary-content rounded-full w-7 h-7 text-xs font-bold flex items-center justify-center shrink-0">
                    <?= esc(strtoupper(substr(session('user_name') ?? 'U', 0, 1))) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate leading-tight"><?= esc(session('user_name') ?? '') ?></p>
                </div>
                <a href="<?= route_to('app.profile') ?>" class="btn btn-ghost btn-xs btn-square <?= str_contains(current_url(), '/app/profile') ? 'btn-active' : '' ?>" title="<?= __('core.profile', 'Profile') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
                <a href="<?= route_to('auth.logout') ?>" class="btn btn-ghost btn-xs btn-square text-error" title="<?= __('core.logout', 'Logout') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </aside>
    </div>
</div>

<?= render('Core::layouts/_scripts') ?>
</body>
</html>
