<?= render('Core::layouts/_head', ['layout_section' => 'front']) ?>
<body class="min-h-screen bg-base-100">

<!-- Navbar -->
<header class="navbar sticky top-0 z-50 bg-base-100/80 backdrop-blur-md border-b border-base-200 px-4 lg:px-8">
    <div class="navbar-start gap-2">
        <?php $logo = option('branding.logo_url', ''); ?>
        <?php if ($logo): ?>
        <a href="<?= base_url() ?>"><img src="<?= esc($logo) ?>" alt="Logo" class="h-8 w-auto object-contain"></a>
        <?php else: ?>
        <a href="<?= base_url() ?>" class="flex items-center gap-2 text-xl font-extrabold tracking-tight">
            <img src="/logo-icon.svg" alt="" class="h-7 w-7">
            <?= esc(option('general.app_name', 'OneShot')) ?>
        </a>
        <?php endif; ?>
    </div>

    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal menu-sm gap-1 px-0">
            <li><a href="#features" class="font-medium"><?= __('core.features', 'Features') ?></a></li>
            <li><a href="#pricing" class="font-medium"><?= __('core.pricing', 'Pricing') ?></a></li>
            <li><a href="#how-it-works" class="font-medium"><?= __('core.how_it_works', 'How it works') ?></a></li>
        </ul>
    </div>

    <div class="navbar-end gap-2">
        <a href="<?= route_to('auth.login') ?>" class="btn btn-ghost btn-sm hidden sm:inline-flex"><?= __('core.login', 'Login') ?></a>
        <a href="<?= route_to('auth.register') ?>" class="btn btn-primary btn-sm"><?= __('core.register', 'Get Started') ?> →</a>

        <!-- Mobile menu -->
        <div class="dropdown dropdown-end lg:hidden">
            <label tabindex="0" class="btn btn-ghost btn-sm btn-square">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </label>
            <ul tabindex="0" class="dropdown-content menu bg-base-200 rounded-box shadow-xl w-48 mt-2 p-2">
                <li><a href="#features"><?= __('core.features', 'Features') ?></a></li>
                <li><a href="#pricing"><?= __('core.pricing', 'Pricing') ?></a></li>
                <li><a href="#how-it-works"><?= __('core.how_it_works', 'How it works') ?></a></li>
                <li><a href="<?= route_to('auth.login') ?>"><?= __('core.login', 'Login') ?></a></li>
            </ul>
        </div>
    </div>
</header>

<?php if (session()->has('success') || session()->has('error') || session()->has('info')): ?>
<div class="container mx-auto px-4 pt-4">
    <?= render('Core::layouts/_flash') ?>
</div>
<?php endif; ?>

<?= $content ?>

<!-- Footer -->
<footer class="border-t border-base-200 mt-24">
    <div class="container mx-auto px-4 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-extrabold tracking-tight"><?= esc(option('general.app_name', 'OneShot')) ?></span>
                <span class="opacity-40 text-sm"><?= __('core.built_on', '— Built on OneShot') ?></span>
            </div>
            <div class="flex gap-6 text-sm opacity-60">
                <a href="#" class="hover:opacity-100 transition-opacity"><?= __('core.privacy', 'Privacy') ?></a>
                <a href="#" class="hover:opacity-100 transition-opacity"><?= __('core.terms', 'Terms') ?></a>
                <a href="<?= route_to('auth.login') ?>" class="hover:opacity-100 transition-opacity"><?= __('core.login', 'Login') ?></a>
            </div>
            <p class="text-sm opacity-40">&copy; <?= date('Y') ?> <?= esc(option('general.app_name', 'OneShot')) ?>. <?= __('core.all_rights', 'All rights reserved.') ?></p>
        </div>
    </div>
</footer>

<?= render('Core::layouts/_scripts') ?>
</body>
</html>
