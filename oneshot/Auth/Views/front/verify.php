<div class="hero min-h-[calc(100vh-4rem)]">
    <div class="hero-content flex-col w-full max-w-md text-center">
<h1 class="text-3xl font-bold"><?= __('auth.verify_title', 'Check your inbox') ?></h1>
        <?php if (! empty($email)): ?>
        <p class="text-base-content/60 mt-1 max-w-sm">
            <?= str_replace('{email}', '<strong>' . esc($email) . '</strong>', __('auth.verify_subtitle', 'We sent a confirmation link to {email}')) ?>
        </p>
        <?php else: ?>
        <p class="text-base-content/60 mt-1"><?= __('auth.verify_subtitle', 'We sent you a confirmation link') ?></p>
        <?php endif ?>

        <div class="card bg-base-200 shadow-xl w-full mt-4">
            <div class="card-body gap-4">
                <p class="text-sm text-base-content/60">
                    <?= __('auth.verify_resend', 'Resend confirmation email') ?>
                </p>
                <form method="post" action="<?= route_to('auth.verify.resend') ?>" class="flex flex-col gap-3">
                    <?= csrf_field() ?>
                    <?php if (! empty($email)): ?>
                    <input type="hidden" name="email" value="<?= esc($email) ?>">
                    <?php else: ?>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.email', 'Email') ?></legend>
                        <input type="email" name="email" class="input input-bordered w-full"
                               placeholder="<?= __('auth.email_placeholder', 'you@example.com') ?>" required>
                    </fieldset>
                    <?php endif ?>
                    <button type="submit" class="btn btn-outline w-full" id="resend-btn">
                        <?= __('auth.verify_resend', 'Resend') ?>
                    </button>
                </form>
                <p class="text-center text-sm text-base-content/60">
                    <a href="<?= route_to('auth.login') ?>" class="link link-primary">← <?= __('auth.login', 'Back to Sign In') ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side 60s cooldown on resend button
(function() {
    const btn = document.getElementById('resend-btn');
    if (! btn) return;
    const form = btn.closest('form');
    const COOLDOWN = 60;

    form.addEventListener('submit', function() {
        let remaining = COOLDOWN;
        btn.disabled = true;
        const orig = btn.textContent.trim();

        const tick = setInterval(function() {
            remaining--;
            btn.textContent = orig + ' (' + remaining + 's)';
            if (remaining <= 0) {
                clearInterval(tick);
                btn.disabled = false;
                btn.textContent = orig;
            }
        }, 1000);
    });
})();
</script>
