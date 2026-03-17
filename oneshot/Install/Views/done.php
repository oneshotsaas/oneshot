<div class="w-full max-w-lg text-center">

    <!-- Steps -->
    <ul class="steps w-full mb-10 text-xs">
        <li class="step step-primary">Database</li>
        <li class="step step-primary">Application</li>
        <li class="step step-primary">Admin</li>
        <li class="step step-primary">Done</li>
    </ul>

    <div class="card bg-base-200 shadow-xl">
        <div class="card-body items-center gap-5 py-12">

            <div class="text-7xl">🎉</div>

            <div>
                <h1 class="text-3xl font-bold mb-2">OneShot installed!</h1>
                <p class="text-base-content/50">Your app is ready. Log in with the admin account you just created.</p>
            </div>

            <!-- AI cleanup notice -->
            <div class="w-full text-left">
                <div class="collapse collapse-arrow bg-base-300 border border-base-content/10">
                    <input type="checkbox" checked />
                    <div class="collapse-title font-semibold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-warning shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Ask your AI to remove the installer gate
                    </div>
                    <div class="collapse-content text-sm text-base-content/70 flex flex-col gap-3">
                        <p>The installer block in <code class="font-mono text-xs bg-base-100 px-1 rounded">app/Config/Routes.php</code> is no longer needed. Send this to your AI assistant:</p>
                        <div class="relative">
                            <button onclick="copyPrompt()" class="btn btn-xs btn-ghost absolute top-2 right-2" id="copy-btn">Copy</button>
                            <pre id="ai-prompt" class="bg-base-100 rounded-lg p-4 pr-16 text-xs font-mono whitespace-pre-wrap leading-relaxed">The app has been installed. In app/Config/Routes.php please remove:
1. The `$routes->get('install/done', ...)` line at the top.
2. The entire `if (! env('app.secretKey')) { ... return; }` block below it — including all install routes and the catch-all redirect.

Leave only the normal app routes (starting from `$routes->get('/', ...)`).</pre>
                        </div>
                    </div>
                </div>
            </div>

            <a href="<?= route_to('auth.login') ?>" class="btn btn-primary btn-lg w-full">Go to login →</a>
        </div>
    </div>
</div>

<script>
function copyPrompt() {
    const text = document.getElementById('ai-prompt').textContent;
    const btn = document.getElementById('copy-btn');
    const done = () => { btn.textContent = 'Copied!'; setTimeout(() => btn.textContent = 'Copy', 2000); };

    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(done);
    } else {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        done();
    }
}
</script>
