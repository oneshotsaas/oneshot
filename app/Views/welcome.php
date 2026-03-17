<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneShot — One prompt. One shot. Ship your SaaS.</title>
    <meta name="description" content="The minimal PHP boilerplate where the AI already knows everything. No docs. No setup. Just describe a feature and ship.">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-base-100">

<!-- NAVBAR -->
<div class="navbar bg-base-200/80 backdrop-blur sticky top-0 z-50 px-6 border-b border-base-300">
    <div class="flex-1">
        <a href="/" class="text-xl font-bold tracking-tight">
            <span class="text-primary">One</span>Shot
        </a>
    </div>
    <div class="flex-none gap-2">
        <a href="<?= route_to('auth.login') ?>" class="btn btn-ghost btn-sm">Sign In</a>
        <a href="<?= route_to('auth.register') ?>" class="btn btn-primary btn-sm">Get Started</a>
    </div>
</div>

<!-- HERO -->
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-base-100 to-base-100 pointer-events-none"></div>
    <div class="container mx-auto px-6 py-24 lg:py-36 text-center relative">
        <div class="badge badge-outline badge-lg mb-6 gap-2">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse inline-block"></span>
            Built for the age of vibe-coding
        </div>
        <h1 class="text-5xl lg:text-7xl font-black tracking-tight leading-tight mb-6">
            One prompt.<br>
            One shot.<br>
            <span class="text-primary">Ship your SaaS.</span>
        </h1>
        <p class="text-xl text-base-content/60 max-w-2xl mx-auto mb-10">
            Describe what you need. Get working code. Skip the docs.<br>
            The framework the AI already knows — so you never start from zero.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?= route_to('auth.register') ?>" class="btn btn-primary btn-lg">
                Get Started
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
            <a href="https://github.com/oneshotsaas/oneshot" target="_blank" class="btn btn-outline btn-lg gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                </svg>
                View on GitHub
            </a>
        </div>
    </div>
</section>

<!-- PROBLEM → SOLUTION STRIP -->
<section class="bg-base-200 border-y border-base-300">
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <p class="text-base-content/40 line-through text-sm mb-1">100MB of framework, 500 pages of docs</p>
                <p class="font-semibold text-base-content">OneShot fits in a single prompt</p>
            </div>
            <div>
                <p class="text-base-content/40 line-through text-sm mb-1">Days reading docs before shipping a feature</p>
                <p class="font-semibold text-base-content">AI writes working code on the first try</p>
            </div>
            <div>
                <p class="text-base-content/40 line-through text-sm mb-1">Re-building auth, billing, users every time</p>
                <p class="font-semibold text-base-content">Pre-built modules. Already verified. Just extend.</p>
            </div>
        </div>
    </div>
</section>

<!-- BENEFITS CARDS -->
<section class="container mx-auto px-6 py-24">
    <div class="text-center mb-16">
        <h2 class="text-4xl font-bold mb-4">Why solo founders choose OneShot</h2>
        <p class="text-base-content/60 max-w-xl mx-auto">Everything you need to go from idea to deployed product — without the noise.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-base-200 border border-base-300 hover:border-primary transition-colors">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="card-title text-xl">The AI Already Knows</h3>
                <p class="text-base-content/60">Every module ships with context the AI reads before writing a single line. No hallucinations. No invented methods. Working code on the first try.</p>
            </div>
        </div>
        <div class="card bg-base-200 border border-base-300 hover:border-primary transition-colors">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="card-title text-xl">Ship the First Draft</h3>
                <p class="text-base-content/60">Auth, users, billing, notifications — already built and verified. Open your project, describe what's next, and ship. No reinventing the wheel.</p>
            </div>
        </div>
        <div class="card bg-base-200 border border-base-300 hover:border-primary transition-colors">
            <div class="card-body">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                </div>
                <h3 class="card-title text-xl">Override Anything</h3>
                <p class="text-base-content/60">Change a view, extend a module, add your own logic — without touching library code. Your project, your rules. Zero framework lock-in.</p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-base-200 border-y border-base-300">
    <div class="container mx-auto px-6 py-24">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4">How it works</h2>
            <p class="text-base-content/60">Three steps from idea to shipped feature.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- connector line (desktop) -->
            <div class="hidden md:block absolute top-8 left-1/3 right-1/3 h-px bg-base-300"></div>
            <div class="flex flex-col items-center text-center gap-4">
                <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-primary-content text-2xl font-black z-10">1</div>
                <h3 class="text-xl font-bold">Describe a feature</h3>
                <p class="text-base-content/60">Open your AI chat. Say what you need. No boilerplate thinking required.</p>
            </div>
            <div class="flex flex-col items-center text-center gap-4">
                <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-primary-content text-2xl font-black z-10">2</div>
                <h3 class="text-xl font-bold">AI reads the codebase</h3>
                <p class="text-base-content/60">The AI knows the architecture, modules, and conventions. It writes code that fits — first time.</p>
            </div>
            <div class="flex flex-col items-center text-center gap-4">
                <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-primary-content text-2xl font-black z-10">3</div>
                <h3 class="text-xl font-bold">Review one file. Ship.</h3>
                <p class="text-base-content/60">You check, you approve, you deploy. That's the whole loop.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA BOTTOM -->
<section class="container mx-auto px-6 py-24 text-center">
    <h2 class="text-4xl lg:text-5xl font-black mb-6">
        Ready to stop reading docs<br>and start shipping?
    </h2>
    <p class="text-base-content/60 mb-10 max-w-lg mx-auto">Clone the repo, run one command, open your AI. Your SaaS is already halfway done.</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="<?= route_to('auth.register') ?>" class="btn btn-primary btn-lg">Start Building Free</a>
        <a href="https://github.com/oneshotsaas/oneshot" target="_blank" class="btn btn-outline btn-lg">View on GitHub</a>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer footer-center bg-base-200 border-t border-base-300 p-8 text-base-content/50">
    <div class="flex flex-col items-center gap-3">
        <p class="font-bold text-base-content">
            <span class="text-primary">One</span>Shot Framework
        </p>
        <p class="text-sm">MIT © <?= date('Y') ?> OneShot Framework</p>
        <div class="flex gap-4 text-sm">
            <a href="https://github.com/oneshotsaas/oneshot" target="_blank" class="link link-hover">GitHub</a>
        </div>
    </div>
</footer>

</body>
</html>
