<div class="max-w-xl">
    <form method="post" action="<?= route_to('app.profile') ?>" class="flex flex-col gap-6">
        <?= csrf_field() ?>

        <div class="card bg-base-200">
            <div class="card-body gap-0">
                <h2 class="card-title text-base mb-4"><?= __('users.account', 'Account') ?></h2>

                <div class="grid grid-cols-[8rem_1fr] items-center gap-x-4 gap-y-3">
                    <span class="text-sm opacity-60"><?= __('users.name', 'Name') ?></span>
                    <input type="text" name="name" value="<?= esc($user->name ?? '') ?>"
                           class="input input-bordered input-sm w-full" required>

                    <span class="text-sm opacity-60"><?= __('users.email', 'Email') ?></span>
                    <input type="text" value="<?= esc($user->email ?? '') ?>"
                           class="input input-bordered input-sm w-full opacity-50 cursor-not-allowed" disabled>
                </div>
            </div>
        </div>

        <div class="card bg-base-200">
            <div class="card-body gap-0">
                <h2 class="card-title text-base mb-4"><?= __('users.appearance', 'Appearance') ?></h2>

                <div class="grid grid-cols-[8rem_1fr] items-center gap-x-4">
                    <span class="text-sm opacity-60"><?= __('users.theme_mode', 'Color scheme') ?></span>
                    <div class="join w-fit">
                        <input class="join-item btn btn-sm" type="radio" name="theme_mode" value="light"
                               aria-label="☀ Light"
                               <?= ($userThemeMode ?? 'dark') === 'light' ? 'checked' : '' ?>>
                        <input class="join-item btn btn-sm" type="radio" name="theme_mode" value="dark"
                               aria-label="🌙 Dark"
                               <?= ($userThemeMode ?? 'dark') === 'dark' ? 'checked' : '' ?>>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary btn-sm px-6"><?= __('users.save', 'Save changes') ?></button>
        </div>
    </form>
</div>
