<div class="hero min-h-[calc(100vh-4rem)]">
    <div class="hero-content flex-col w-full max-w-md">
        <div class="text-center">
<h1 class="text-3xl font-bold"><?= __('auth.forgot_title', 'Reset your password') ?></h1>
            <p class="text-base-content/60 mt-1"><?= __('auth.forgot_subtitle', "Enter your email and we'll send you a reset link") ?></p>
        </div>
        <div class="card bg-base-200 shadow-xl w-full">
            <div class="card-body gap-4">
                <form method="post" action="<?= route_to('auth.forgot') ?>" class="flex flex-col gap-4">
                    <?= csrf_field() ?>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.email', 'Email') ?></legend>
                        <input type="email" name="email" class="input input-bordered w-full"
                               placeholder="<?= __('auth.email_placeholder', 'you@example.com') ?>" required autocomplete="email">
                    </fieldset>
                    <button type="submit" class="btn btn-primary w-full"><?= __('auth.forgot_submit', 'Send Reset Link') ?></button>
                </form>
                <p class="text-center text-sm text-base-content/60">
                    <a href="<?= route_to('auth.login') ?>" class="link link-primary">
                        ← <?= __('auth.login', 'Back to Sign In') ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
