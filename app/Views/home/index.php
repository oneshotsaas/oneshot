<?php $appName = option('general.app_name', 'OneShot'); ?>

<!-- Hero -->
<section class="relative overflow-hidden min-h-[90vh] flex items-center">
    <!-- Background glow -->
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-primary/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-secondary/10 blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-24">
        <div class="max-w-4xl mx-auto text-center">
            <div class="badge badge-outline badge-lg mb-6 gap-2 font-medium">
                <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                Now in early access
            </div>
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight leading-[1.05] mb-6">
                The platform that<br>
                <span class="text-primary">ships with you</span>
            </h1>
            <p class="text-xl opacity-60 max-w-2xl mx-auto mb-10 leading-relaxed">
                Everything you need to launch, grow and monetize your SaaS product.
                Authentication, billing, settings and beautiful UI — ready from day one.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="<?= route_to('auth.register') ?>" class="btn btn-primary btn-lg px-8">
                    Get Started Free →
                </a>
                <a href="<?= route_to('auth.login') ?>" class="btn btn-ghost btn-lg px-8">
                    Sign in
                </a>
            </div>
            <p class="text-sm opacity-40 mt-5">No credit card required. Free forever plan available.</p>
        </div>

        <!-- Mock UI preview -->
        <div class="mt-20 max-w-5xl mx-auto">
            <div class="mockup-browser border border-base-300 bg-base-200 shadow-2xl">
                <div class="mockup-browser-toolbar">
                    <div class="input bg-base-300 text-sm opacity-60">app.yourproduct.com/dashboard</div>
                </div>
                <div class="bg-base-100 p-6 flex gap-4 min-h-[200px]">
                    <div class="w-40 bg-base-200 rounded-lg p-3 flex flex-col gap-2">
                        <div class="h-4 bg-primary/20 rounded w-3/4"></div>
                        <?php for ($i = 0; $i < 5; $i++): ?>
                        <div class="h-3 bg-base-300 rounded w-full"></div>
                        <?php endfor; ?>
                    </div>
                    <div class="flex-1 flex flex-col gap-3">
                        <div class="grid grid-cols-3 gap-3">
                            <?php foreach (['bg-primary/20', 'bg-secondary/20', 'bg-accent/20'] as $c): ?>
                            <div class="<?= $c ?> rounded-xl p-4">
                                <div class="h-3 bg-base-content/10 rounded w-1/2 mb-2"></div>
                                <div class="h-6 bg-base-content/15 rounded w-2/3"></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="bg-base-200 rounded-xl p-4 flex-1">
                            <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="flex gap-3 mb-2 items-center">
                                <div class="w-6 h-6 rounded-full bg-base-300 shrink-0"></div>
                                <div class="h-3 bg-base-300 rounded flex-1"></div>
                                <div class="h-3 bg-base-300 rounded w-16"></div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-24 bg-base-200/50" id="features">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold tracking-tight mb-4">Everything included</h2>
            <p class="text-lg opacity-60 max-w-xl mx-auto">Stop rebuilding the same foundation. <?= esc($appName) ?> gives you all the boring parts so you can focus on what matters.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $features = [
                ['🔐', 'Authentication', 'Login, register, password reset and role-based access control out of the box.'],
                ['🎨', 'Themeable UI', '32+ DaisyUI themes, dark/light mode per section, custom CSS from the theme generator.'],
                ['⚙️', 'Settings Module', 'Global and per-user options with encryption at rest. Extensible for any config.'],
                ['📦', 'Modular Architecture', 'Add or override any module without touching the core. modules/ always wins.'],
                ['🚀', 'One-click Install', 'Guided installer configures DB, runs migrations, creates admin and seals the app.'],
                ['🌍', 'i18n Ready', 'Multi-language support built in with simple __() helpers throughout.'],
            ];
            foreach ($features as [$icon, $title, $desc]):
            ?>
            <div class="card bg-base-100 border border-base-200 hover:border-primary/30 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body gap-3">
                    <div class="text-3xl"><?= $icon ?></div>
                    <h3 class="text-lg font-bold"><?= esc($title) ?></h3>
                    <p class="text-sm opacity-60 leading-relaxed"><?= esc($desc) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-24" id="how-it-works">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold tracking-tight mb-4">Up and running in minutes</h2>
            <p class="text-lg opacity-60">Three steps from zero to production-ready SaaS.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php
            $steps = [
                ['01', 'Clone & Install', 'Run composer install and open /install. The guided wizard configures your database, environment and admin account.'],
                ['02', 'Customize', 'Pick your theme, add your brand, configure settings. Build your modules in the modules/ directory.'],
                ['03', 'Ship', 'Deploy to any PHP host. Your users register, subscribe and get to work in your app.'],
            ];
            foreach ($steps as [$num, $title, $desc]):
            ?>
            <div class="flex flex-col items-center text-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-extrabold text-xl">
                    <?= $num ?>
                </div>
                <h3 class="text-xl font-bold"><?= esc($title) ?></h3>
                <p class="text-sm opacity-60 leading-relaxed"><?= esc($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Pricing -->
<section class="py-24 bg-base-200/50" id="pricing">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold tracking-tight mb-4">Simple pricing</h2>
            <p class="text-lg opacity-60">Start free, scale when you need to.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <?php
            $plans = [
                ['Free', '$0', '/mo', ['Up to 3 users', '1 project', 'Community support', 'All core features'], false],
                ['Pro',  '$29', '/mo', ['Unlimited users', '10 projects', 'Priority support', 'Advanced analytics', 'Custom domain'], true],
                ['Business', '$99', '/mo', ['Everything in Pro', 'Unlimited projects', 'SLA guarantee', 'Dedicated support', 'SSO / SAML'], false],
            ];
            foreach ($plans as [$name, $price, $period, $feat, $featured]):
            ?>
            <div class="card <?= $featured ? 'bg-primary text-primary-content border-primary shadow-xl scale-105' : 'bg-base-100 border border-base-200' ?> border">
                <div class="card-body gap-4">
                    <div>
                        <h3 class="text-lg font-bold <?= $featured ? '' : '' ?>"><?= esc($name) ?></h3>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-4xl font-extrabold"><?= esc($price) ?></span>
                            <span class="opacity-60"><?= esc($period) ?></span>
                        </div>
                    </div>
                    <ul class="flex flex-col gap-2 text-sm">
                        <?php foreach ($feat as $f): ?>
                        <li class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 <?= $featured ? 'opacity-90' : 'text-primary' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?= esc($f) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= route_to('auth.register') ?>"
                       class="btn btn-sm mt-2 <?= $featured ? 'bg-primary-content text-primary hover:bg-primary-content/90' : 'btn-primary' ?> w-full">
                        Get started
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-24">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-3xl mx-auto text-center rounded-3xl bg-primary/10 border border-primary/20 p-16">
            <h2 class="text-4xl font-extrabold tracking-tight mb-4">Start building today</h2>
            <p class="text-lg opacity-60 mb-8">Join developers who ship faster with <?= esc($appName) ?>.</p>
            <a href="<?= route_to('auth.register') ?>" class="btn btn-primary btn-lg px-10">
                Create free account →
            </a>
        </div>
    </div>
</section>
