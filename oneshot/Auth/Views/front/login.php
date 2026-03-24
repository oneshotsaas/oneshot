<div class="hero min-h-[calc(100vh-4rem)]">
    <div class="hero-content flex-col w-full max-w-md">
        <div class="text-center">
            <h1 class="text-3xl font-bold"><?= __('auth.welcome_back', 'Welcome back') ?></h1>
            <p class="text-base-content/60 mt-1"><?= __('auth.sign_in_subtitle', 'Sign in to your account') ?></p>
        </div>
        <div class="card bg-base-200 shadow-xl w-full">
            <div class="card-body gap-4">
                <form method="post" action="<?= route_to('auth.login') ?>" class="flex flex-col gap-4">
                    <?= csrf_field() ?>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.email', 'Email') ?></legend>
                        <input type="email" name="email" class="input input-bordered w-full"
                               placeholder="<?= __('auth.email_placeholder', 'you@example.com') ?>" required autocomplete="email">
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.password', 'Password') ?></legend>
                        <input type="password" name="password" class="input input-bordered w-full"
                               placeholder="<?= __('auth.password_placeholder', '••••••••') ?>" required autocomplete="current-password">
                    </fieldset>
                    <div class="flex justify-end -mt-2">
                        <a href="<?= route_to('auth.forgot') ?>" class="link link-primary text-sm">
                            <?= __('auth.forgot_password', 'Forgot password?') ?>
                        </a>
                    </div>
                    <button type="submit" class="btn btn-primary w-full"><?= __('auth.login', 'Sign In') ?></button>
                </form>

                <?= render('Auth::front/oauth_buttons', ['oauthProviders' => $oauthProviders ?? []]) ?>

                <p class="text-center text-sm text-base-content/60">
                    <?= __('auth.no_account', "Don't have an account?") ?>
                    <a href="<?= route_to('auth.register') ?>" class="link link-primary"><?= __('auth.register', 'Get Started') ?></a>
                </p>
            </div>
        </div>
    </div>
</div>
