<div class="w-full max-w-lg">

    <div class="text-center mb-8">
        <div class="text-4xl font-black tracking-tight mb-1">OneShot</div>
        <div class="text-base-content/50 text-sm">Installation wizard · Step 3 of 3</div>
    </div>

    <ul class="steps w-full mb-8 text-xs">
        <li class="step step-primary">Database</li>
        <li class="step step-primary">Application</li>
        <li class="step step-primary">Admin</li>
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

    <form method="post" action="<?= route_to('install.finish') ?>">
        <?= csrf_field() ?>

        <div class="card bg-base-200 shadow-xl mb-6">
            <div class="card-body gap-4">
                <h2 class="card-title">Admin account</h2>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Full name</legend>
                    <input type="text" name="name" class="input input-bordered w-full"
                           value="<?= esc($saved['name'] ?? '') ?>" placeholder="John Doe" required />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Email</legend>
                    <input type="email" name="email" class="input input-bordered w-full"
                           value="<?= esc($saved['email'] ?? '') ?>" placeholder="admin@example.com" required />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Password</legend>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="password" name="password" id="pwd"
                                   class="input input-bordered w-full pr-10"
                                   value="<?= esc($saved['password'] ?? '') ?>"
                                   placeholder="<?= ! empty($saved['password']) ? '● saved — leave blank to keep' : '••••••••' ?>"
                                   minlength="8" required />
                            <button type="button" id="pwd-toggle"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-xs btn-ghost px-1"
                                    title="Show / hide">
                                <svg id="eye-show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eye-hide" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <button type="button" id="pwd-gen" class="btn btn-outline btn-sm self-center whitespace-nowrap">Generate</button>
                    </div>
                    <ul class="mt-2 flex flex-col gap-0.5 text-xs text-base-content/60" id="pwd-reqs">
                        <li id="req-len"   class="flex items-center gap-1.5"><span class="req-dot">○</span> At least 8 characters</li>
                        <li id="req-upper" class="flex items-center gap-1.5"><span class="req-dot">○</span> One uppercase letter (A–Z)</li>
                        <li id="req-digit" class="flex items-center gap-1.5"><span class="req-dot">○</span> One digit (0–9)</li>
                        <li id="req-spec"  class="flex items-center gap-1.5"><span class="req-dot">○</span> One special character (!@#$…)</li>
                    </ul>
                    <p class="fieldset-label mt-1" id="pwd-hint"></p>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Timezone</legend>
                    <select name="timezone" class="select select-bordered w-full">
                        <?php foreach ($timezones as $tz): ?>
                        <option value="<?= esc($tz) ?>" <?= $tz === ($saved['timezone'] ?? 'UTC') ? 'selected' : '' ?>>
                            <?= esc($tz) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </fieldset>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="<?= route_to('install.back-app') ?>" class="btn btn-ghost flex-none">← Back</a>
            <button type="submit" class="btn btn-primary flex-1">Install OneShot 🚀</button>
        </div>
    </form>
</div>

<script>
(function () {
    const pwd    = document.getElementById('pwd');
    const hint   = document.getElementById('pwd-hint');
    const toggle = document.getElementById('pwd-toggle');
    const eyeShow= document.getElementById('eye-show');
    const eyeHide= document.getElementById('eye-hide');
    const btnGen = document.getElementById('pwd-gen');
    const reqs   = {
        len:   { el: document.getElementById('req-len'),   fn: v => v.length >= 8 },
        upper: { el: document.getElementById('req-upper'), fn: v => /[A-Z]/.test(v) },
        digit: { el: document.getElementById('req-digit'), fn: v => /[0-9]/.test(v) },
        spec:  { el: document.getElementById('req-spec'),  fn: v => /[^A-Za-z0-9]/.test(v) },
    };

    function updateReqs(val) {
        let met = 0;
        for (const r of Object.values(reqs)) {
            const ok = r.fn(val);
            r.el.querySelector('.req-dot').textContent = ok ? '✓' : '○';
            r.el.classList.toggle('text-success',        ok);
            r.el.classList.toggle('text-base-content/60', !ok);
            if (ok) met++;
        }
        if (!val.length) { hint.textContent = ''; return; }
        const labels = ['', 'Weak', 'Weak', 'Acceptable', 'Strong'];
        const colors = ['', 'text-error', 'text-error', 'text-warning', 'text-success'];
        hint.textContent = labels[met];
        hint.className   = 'fieldset-label mt-1 ' + colors[met];
    }

    pwd.addEventListener('input', () => updateReqs(pwd.value));
    updateReqs('');

    toggle.addEventListener('click', () => {
        const show = pwd.type === 'password';
        pwd.type = show ? 'text' : 'password';
        eyeShow.classList.toggle('hidden', show);
        eyeHide.classList.toggle('hidden', !show);
    });

    btnGen.addEventListener('click', () => {
        const upper  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lower  = 'abcdefghijklmnopqrstuvwxyz';
        const digits = '0123456789';
        const spec   = '!@#$%^&*()-_=+[]{}';
        const all    = upper + lower + digits + spec;
        const arr    = new Uint8Array(16);
        crypto.getRandomValues(arr);
        let pass = upper[arr[0] % upper.length]
                 + digits[arr[1] % digits.length]
                 + spec[arr[2]   % spec.length];
        for (let i = 3; i < 16; i++) pass += all[arr[i] % all.length];
        pass = pass.split('').sort(() => Math.random() - 0.5).join('');
        pwd.value = pass;
        pwd.type  = 'text';
        eyeShow.classList.add('hidden');
        eyeHide.classList.remove('hidden');
        updateReqs(pass);
    });
})();
</script>
