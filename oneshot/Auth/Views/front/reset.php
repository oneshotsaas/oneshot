<div class="hero min-h-[calc(100vh-4rem)]">
    <div class="hero-content flex-col w-full max-w-md">
        <div class="text-center">
            <h1 class="text-3xl font-bold"><?= __('auth.reset_title', 'Set new password') ?></h1>
            <p class="text-base-content/60 mt-1"><?= __('auth.reset_subtitle', 'Choose a strong password') ?></p>
        </div>
        <div class="card bg-base-200 shadow-xl w-full">
            <div class="card-body gap-4">
                <form method="post" action="<?= current_url() ?>" class="flex flex-col gap-4">
                    <?= csrf_field() ?>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.new_password', 'New Password') ?></legend>
                        <div class="pw-wrap relative">
                            <input type="password" name="password" class="input input-bordered w-full pr-24"
                                   placeholder="<?= __('auth.password_placeholder', '••••••••') ?>"
                                   required autocomplete="new-password" minlength="<?= (int)($passwordPolicy['min_length'] ?? 8) ?>">
                            <div class="absolute right-1 top-1/2 -translate-y-1/2 flex gap-0.5">
                                <div class="tooltip tooltip-top" data-tip="<?= __('auth.pw_show', 'Show') ?>">
                                    <button type="button" class="js-pw-toggle btn btn-ghost btn-xs px-1.5 opacity-50 hover:opacity-100"
                                            tabindex="-1"
                                            data-label-show="<?= __('auth.pw_show', 'Show') ?>"
                                            data-label-hide="<?= __('auth.pw_hide', 'Hide') ?>">
                                        <svg class="icon-eye w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="icon-eye-off w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                <div class="tooltip tooltip-top" data-tip="<?= __('auth.pw_copy', 'Copy') ?>">
                                    <button type="button" class="js-copy-value btn btn-ghost btn-xs px-1.5 opacity-50 hover:opacity-100" tabindex="-1">
                                        <svg class="icon-copy w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <svg class="icon-check w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </div>
                                <div class="tooltip tooltip-top" data-tip="<?= __('auth.pw_generate', 'Generate') ?>">
                                    <button type="button" class="js-pw-generate btn btn-ghost btn-xs px-1.5 opacity-50 hover:opacity-100" tabindex="-1">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php if (! empty($passwordPolicy)): ?>
                        <?php $p = $passwordPolicy; ?>
                        <p class="text-xs text-base-content/40 mt-1 flex flex-wrap gap-x-2">
                            <span data-rule="min_length"><?= sprintf(esc(__('auth.pw_rule_min', 'at least %d chars')), $p['min_length']) ?></span>
                            <?php if ($p['uppercase']): ?><span data-rule="uppercase"><?= esc(__('auth.pw_rule_uppercase', 'upper & lowercase')) ?></span><?php endif; ?>
                            <?php if ($p['numbers']): ?><span data-rule="numbers"><?= esc(__('auth.pw_rule_numbers', 'a number')) ?></span><?php endif; ?>
                            <?php if ($p['symbols']): ?><span data-rule="symbols"><?= esc(__('auth.pw_rule_symbols', 'a special character')) ?></span><?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend"><?= __('auth.confirm_password', 'Confirm Password') ?></legend>
                        <input type="password" name="confirm_password" class="input input-bordered w-full"
                               placeholder="<?= __('auth.password_placeholder', '••••••••') ?>"
                               required autocomplete="new-password">
                    </fieldset>
                    <button type="submit" class="btn btn-primary w-full mt-2"><?= __('auth.reset_submit', 'Reset Password') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
