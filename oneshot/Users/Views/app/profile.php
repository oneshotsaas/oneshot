<div class="max-w-xl">
    <form method="post" action="<?= route_to('app.profile') ?>" class="flex flex-col gap-6">
        <?= csrf_field() ?>

        <div class="card bg-base-200">
            <div class="card-body gap-0">
                <h2 class="card-title text-base mb-4"><?= __('users.account', 'Account') ?></h2>

                <div class="flex flex-col gap-3">
                    <div class="grid grid-cols-1 sm:grid-cols-[8rem_1fr] items-center gap-x-4 gap-y-1">
                        <span class="text-sm opacity-60"><?= __('users.name', 'Name') ?></span>
                        <input type="text" name="name" value="<?= esc($user->name ?? '') ?>"
                               class="input input-bordered input-sm w-full" required>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-[8rem_1fr] items-center gap-x-4 gap-y-1">
                        <span class="text-sm opacity-60"><?= __('users.email', 'Email') ?></span>
                        <input type="text" value="<?= esc($user->email ?? '') ?>"
                               class="input input-bordered input-sm w-full opacity-50 cursor-not-allowed" disabled>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-[8rem_1fr] items-start gap-x-4 gap-y-1">
                        <span class="text-sm opacity-60 pt-1"><?= __('notifications.telegram_id', 'Telegram ID') ?></span>
                        <div>
                            <input type="text" name="telegram_id" value="<?= esc($user->telegram_id ?? '') ?>"
                                   class="input input-bordered input-sm w-full" placeholder="123456789">
                            <p class="text-xs opacity-40 mt-1"><?= __('notifications.telegram_id_hint', 'Your Telegram user ID. Write to the bot and send /start — it will reply with your ID.') ?></p>
                        </div>
                    </div>
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

    <?php if (!empty($notifTypes->groups) && !empty($notifTypes->types)): ?>
    <div class="card bg-base-200 mt-6">
        <div class="card-body gap-0">
            <h2 class="card-title text-base mb-4"><?= __('notifications.preferences', 'Notification Preferences') ?></h2>

            <?php foreach ($notifTypes->groups as $group => $groupLabel): ?>
                <?php
                    $groupTypes = array_filter($notifTypes->types, fn($cfg) => ($cfg['group'] ?? '') === $group);
                    if (empty($groupTypes)) continue;
                    // Collect channels used in this group
                    $groupChannels = [];
                    foreach ($groupTypes as $cfg) {
                        foreach ($cfg['channels'] as $ch) {
                            $groupChannels[$ch] = true;
                        }
                    }
                    $groupChannels = array_keys($groupChannels);
                ?>
                <div class="mb-5">
                    <p class="text-xs font-semibold uppercase opacity-40 mb-2">
                        <?= __('notifications.group_' . $group, ucfirst($group)) ?>
                    </p>
                    <div class="flex flex-col gap-2">
                        <?php foreach ($groupTypes as $typeKey => $cfg): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-sm flex-1">
                                <?= __('notifications.type_' . str_replace('.', '_', $typeKey), $cfg['label']) ?>
                            </span>
                            <?php foreach ($cfg['channels'] as $channel): ?>
                                <?php $enabled = $notifPrefs[$typeKey][$channel] ?? false; ?>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox"
                                           class="toggle toggle-xs notif-pref-toggle"
                                           data-type="<?= esc($typeKey) ?>"
                                           data-channel="<?= esc($channel) ?>"
                                           <?= $enabled ? 'checked' : '' ?>>
                                    <span class="text-xs opacity-60">
                                        <?= __('notifications.channel_' . $channel, ucfirst($channel)) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
