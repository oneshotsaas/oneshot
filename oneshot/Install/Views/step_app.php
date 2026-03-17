<div class="w-full max-w-lg">

    <div class="text-center mb-8">
        <div class="text-4xl font-black tracking-tight mb-1">OneShot</div>
        <div class="text-base-content/50 text-sm">Installation wizard · Step 2 of 3</div>
    </div>

    <ul class="steps w-full mb-8 text-xs">
        <li class="step step-primary">Database</li>
        <li class="step step-primary">Application</li>
        <li class="step">Admin</li>
        <li class="step">Done</li>
    </ul>

    <?php if ($error): ?>
    <div class="alert alert-error mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span><?= esc($error) ?></span>
    </div>
    <?php endif ?>

    <form method="post" action="<?= route_to('install.save-app') ?>">
        <?= csrf_field() ?>

        <div class="card bg-base-200 shadow-xl mb-6">
            <div class="card-body gap-4">
                <h2 class="card-title">Application</h2>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">App name</legend>
                    <input type="text" name="app_name" class="input input-bordered w-full"
                           value="<?= esc($app_name) ?>" required />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Base URL</legend>
                    <input type="url" name="base_url" id="base-url"
                           class="input input-bordered w-full font-mono text-sm"
                           value="<?= esc($base_url) ?>" required />
                </fieldset>

                <!-- HTTPS notice -->
                <div id="https-notice" class="hidden">
                    <div class="collapse collapse-arrow bg-base-300 border border-base-content/10">
                        <input type="checkbox" />
                        <div class="collapse-title text-sm font-medium flex items-center gap-2 py-2 min-h-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-info shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            HTTPS recommended for production
                        </div>
                        <div class="collapse-content text-sm text-base-content/70 flex flex-col gap-3">
                            <p>HTTP is fine locally. For a live site, HTTPS is required for secure cookies, improves SEO, and protects traffic. When you get an SSL certificate, ask your AI:</p>
                            <div class="relative">
                                <pre id="https-prompt" class="bg-base-100 rounded-lg p-3 text-xs font-mono whitespace-pre-wrap leading-relaxed">The site now runs on HTTPS. Please update app.baseURL in .env from http:// to https://, and set app.forceGlobalSecureRequests = true so all requests are redirected to HTTPS automatically.</pre>
                                <button type="button" onclick="copyText('https-prompt', this)" class="btn btn-xs btn-ghost absolute top-2 right-2">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Environment</legend>
                    <div class="flex gap-4 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="environment" value="production" class="radio radio-primary" id="env-prod"
                                   <?= $env === 'production' ? 'checked' : '' ?> />
                            <span>Production</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="environment" value="development" class="radio" id="env-dev"
                                   <?= $env !== 'production' ? 'checked' : '' ?> />
                            <span>Development</span>
                        </label>
                    </div>
                </fieldset>

                <!-- Environment notice -->
                <div id="env-notice-dev" class="collapse collapse-arrow bg-base-300 border border-base-content/10 text-sm">
                    <input type="checkbox" checked />
                    <div class="collapse-title font-medium flex items-center gap-2 py-2 min-h-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-success shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Development mode — what's enabled
                    </div>
                    <div class="collapse-content text-base-content/70">
                        <ul class="list-none flex flex-col gap-1 mt-1">
                            <li>✅ <strong>Errors on screen</strong> — full stack traces in the browser</li>
                            <li>✅ <strong>Debug toolbar</strong> — queries, routes, timings, vars</li>
                            <li>✅ <strong>Verbose logging</strong> — all levels to <code class="text-xs bg-base-100 px-1 rounded">writable/logs/</code></li>
                            <li>✅ <strong>Cache disabled</strong> — views and config reloaded fresh every request</li>
                        </ul>
                        <p class="mt-2 text-xs text-warning">For local development only. Never deploy with this mode on a public server.</p>
                    </div>
                </div>

                <div id="env-notice-prod" class="hidden collapse collapse-arrow bg-base-300 border border-base-content/10 text-sm">
                    <input type="checkbox" checked />
                    <div class="collapse-title font-medium flex items-center gap-2 py-2 min-h-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Production mode — what's enabled
                    </div>
                    <div class="collapse-content text-base-content/70">
                        <ul class="list-none flex flex-col gap-1 mt-1">
                            <li>🔒 <strong>Errors hidden</strong> — generic 500 page shown, details in logs only</li>
                            <li>🔒 <strong>Debug toolbar disabled</strong> — no internal info exposed to visitors</li>
                            <li>🔒 <strong>Minimal logging</strong> — critical errors only</li>
                            <li>🔒 <strong>Caching active</strong> — config and routes cached for speed</li>
                        </ul>
                        <p class="mt-2 text-xs text-info">Correct choice for a live public server.</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Theme mode -->
        <div class="card bg-base-200 shadow-xl mb-6">
            <div class="card-body gap-3">
                <h2 class="card-title">Default Theme</h2>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Color scheme</legend>
                    <div class="flex gap-4 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="theme_mode" value="light" class="radio radio-primary"
                                   <?= ($theme_mode ?? 'dark') === 'light' ? 'checked' : '' ?>>
                            <span>☀ Light</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="theme_mode" value="dark" class="radio radio-primary"
                                   <?= ($theme_mode ?? 'dark') === 'dark' ? 'checked' : '' ?>>
                            <span>🌙 Dark</span>
                        </label>
                    </div>
                    <p class="text-xs opacity-50 mt-2">More themes and custom CSS available in Admin → Settings → Appearance after install.</p>
                </fieldset>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="<?= route_to('install.back-db') ?>" class="btn btn-ghost flex-none">← Back</a>
            <button type="submit" class="btn btn-primary flex-1">Continue →</button>
        </div>
    </form>
</div>

<script>
(function () {
    // HTTPS notice
    const baseUrlInput = document.getElementById('base-url');
    const httpsNotice  = document.getElementById('https-notice');
    function checkHttps() {
        httpsNotice.classList.toggle('hidden', !baseUrlInput.value.trim().startsWith('http://'));
    }
    baseUrlInput.addEventListener('input', checkHttps);
    checkHttps();

    // Environment notice
    const envProd    = document.getElementById('env-prod');
    const envDev     = document.getElementById('env-dev');
    const noticeDev  = document.getElementById('env-notice-dev');
    const noticeProd = document.getElementById('env-notice-prod');
    function switchEnv() {
        noticeProd.classList.toggle('hidden', !envProd.checked);
        noticeDev.classList.toggle('hidden',   envProd.checked);
    }
    envProd.addEventListener('change', switchEnv);
    envDev.addEventListener('change',  switchEnv);
    switchEnv();

    window.copyText = function (id, btn) {
        navigator.clipboard.writeText(document.getElementById(id).textContent).then(() => {
            const orig = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = orig, 2000);
        });
    };
})();
</script>
