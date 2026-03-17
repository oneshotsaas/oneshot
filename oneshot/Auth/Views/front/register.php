<div class="hero min-h-[calc(100vh-4rem)]">
    <div class="hero-content flex-col w-full max-w-sm">
        <div class="text-center">
            <h1 class="text-3xl font-bold"><?= __('auth.create_account', 'Create account') ?></h1>
            <p class="text-base-content/60 mt-1"><?= __('auth.register_subtitle', 'Start building your SaaS today') ?></p>
        </div>
        <div class="card bg-base-200 shadow-xl w-full">
            <div class="card-body gap-4">
                <form method="post" action="<?= route_to('auth.register') ?>" class="flex flex-col gap-4">
                    <?= csrf_field() ?>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.name', 'Name') ?></legend>
                        <input type="text" name="name" class="input input-bordered w-full" placeholder="<?= __('auth.name_placeholder', 'Your name') ?>" required>
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.email', 'Email') ?></legend>
                        <input type="email" name="email" class="input input-bordered w-full" placeholder="<?= __('auth.email_placeholder', 'you@example.com') ?>" required>
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.password', 'Password') ?></legend>
                        <input type="password" name="password" class="input input-bordered w-full" placeholder="<?= __('auth.password_placeholder', '••••••••') ?>" required>
                    </fieldset>
                    <button type="submit" class="btn btn-primary w-full mt-2"><?= __('auth.register', 'Create Account') ?></button>
                </form>
                <p class="text-center text-sm text-base-content/60">
                    <?= __('auth.have_account', 'Already have an account?') ?>
                    <a href="<?= route_to('auth.login') ?>" class="link link-primary"><?= __('auth.login', 'Sign In') ?></a>
                </p>
            </div>
        </div>
    </div>
</div>
