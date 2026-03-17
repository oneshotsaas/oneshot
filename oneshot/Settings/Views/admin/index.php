<?php
/**
 * Settings admin page
 * Vars: $groups, $activeGroup, $fields, $groupLabel
 */

// Build a map key → current value (decrypted, already passed as field objects from DB)
$values = [];
foreach ($fields as $f) {
    $values[$f->key] = option($f->key, '');
}

$isAppearance = $activeGroup === 'appearance';
$sections     = ['admin', 'app', 'front'];
$modes        = ['light', 'dark'];

// For appearance: preload all theme CSS files so swatches render correctly
if ($isAppearance):
    $allThemes = [
        'light','cupcake','bumblebee','emerald','corporate','retro','valentine',
        'garden','forest','aqua','lofi','pastel','fantasy','wireframe','cmyk',
        'autumn','lemonade','winter',
        'dark','synthwave','cyberpunk','halloween','black','luxury','dracula',
        'business','acid','night','coffee','dim','nord','sunset',
    ];
    foreach ($allThemes as $_t):
        $_tf = FCPATH . 'assets/css/themes/' . $_t . '.css';
        if (is_file($_tf)) {
            echo '<link rel="stylesheet" href="' . base_url('assets/css/themes/' . $_t . '.css') . '?v=' . filemtime($_tf) . '">' . "\n";
        }
    endforeach;
endif;
?>

<div data-live-theme="1" id="settings-page">
<div class="flex gap-6 min-h-0">

    <!-- Group nav -->
    <aside class="w-44 shrink-0">
        <ul class="menu menu-sm bg-base-200 rounded-xl p-2 gap-0.5 sticky top-20">
            <?php foreach ($groups as $g): ?>
            <li>
                <a href="<?= route_to('admin.settings.group', $g) ?>"
                   class="<?= $g === $activeGroup ? 'active font-semibold' : '' ?> capitalize">
                    <?= esc(ucfirst($g)) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Params form -->
    <div class="flex-1 min-w-0">

        <form method="POST" action="<?= route_to('admin.settings.group', $activeGroup) ?>">
            <?= csrf_field() ?>

            <?php if ($isAppearance): ?>
            <!-- ====== APPEARANCE UI ====== -->

            <!-- Default modes per section -->
            <div class="card bg-base-200 rounded-xl mb-6">
                <div class="card-body gap-4">
                    <div>
                        <h3 class="font-semibold text-sm"><?= __('settings.default_mode_per_section', 'Default mode per section') ?></h3>
                        <p class="text-xs opacity-40 mt-0.5"><?= __('settings.default_mode_hint', 'Choose which color mode (light or dark) each section of the site uses by default.') ?></p>
                    </div>
                    <div class="flex flex-col gap-3">
                        <?php
                        $sectionLabels = [
                            'admin' => [__('settings.section_admin', 'Admin'), __('settings.section_admin_hint', 'admin panel (you are here)')],
                            'app'   => [__('settings.section_app',   'App'),   __('settings.section_app_hint',   'user account area')],
                            'front' => [__('settings.section_front', 'Front'), __('settings.section_front_hint', 'public product site')],
                        ];
                        ?>
                        <?php foreach ($sections as $sec): ?>
                        <?php $modeKey = "appearance.{$sec}_default_mode"; $curMode = $values[$modeKey] ?? 'dark'; ?>
                        <div class="flex items-center gap-x-4">
                            <span class="shrink-0 w-36">
                                <span class="text-sm font-medium block"><?= esc($sectionLabels[$sec][0]) ?></span>
                                <span class="text-xs opacity-40"><?= esc($sectionLabels[$sec][1]) ?></span>
                            </span>
                            <div class="join w-fit">
                                <?php foreach ($modes as $m): ?>
                                <input class="join-item btn btn-sm"
                                       type="radio"
                                       name="<?= esc(str_replace('.', '__', $modeKey)) ?>"
                                       value="<?= esc($m) ?>"
                                       aria-label="<?= esc(ucfirst($m)) ?>"
                                       <?= $curMode === $m ? 'checked' : '' ?>>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Section pills: Admin / App / Front -->
            <div class="flex gap-1 bg-base-200 p-1 rounded-xl w-fit mb-4" id="section-tabs">
                <?php foreach ($sections as $si => $sec): ?>
                <button type="button"
                        class="section-tab cursor-pointer px-5 py-1.5 rounded-lg text-sm font-medium transition-all capitalize
                               <?= $si === 0 ? 'bg-base-100 shadow text-base-content' : 'text-base-content/50 hover:text-base-content/80' ?>"
                        data-sec="<?= esc($sec) ?>">
                    <?= esc(ucfirst($sec)) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <p class="text-xs opacity-40 mb-4"><?= __('settings.appearance_hint', 'Select a section, then switch between Light and Dark — each has its own theme and custom CSS.') ?></p>

            <!-- Section panels -->
            <?php foreach ($sections as $si => $sec): ?>
            <div class="section-panel bg-base-200 rounded-xl p-5" id="panel-<?= esc($sec) ?>" <?= $si !== 0 ? 'hidden' : '' ?>>

                <!-- Mode toggle: Light / Dark -->
                <div class="flex gap-1 bg-base-300/50 p-1 rounded-lg w-fit mb-5" id="mode-tabs-<?= esc($sec) ?>">
                    <?php foreach ($modes as $mi => $mode): ?>
                    <button type="button"
                            class="mode-tab cursor-pointer px-4 py-1 rounded-md text-sm font-medium transition-all
                                   <?= $mi === 0 ? 'bg-base-100 shadow text-base-content' : 'text-base-content/50 hover:text-base-content/80' ?>"
                            data-sec="<?= esc($sec) ?>"
                            data-mode="<?= esc($mode) ?>">
                        <?= $mode === 'light' ? __('settings.mode_light', '☀️ Light') : __('settings.mode_dark', '🌙 Dark') ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Mode panels -->
                <?php foreach ($modes as $mi => $mode): ?>
                <?php
                $themeKey  = "appearance.{$sec}_{$mode}_theme";
                $pickerId  = "picker_{$sec}_{$mode}";
                $fieldName = str_replace('.', '__', $themeKey);
                $current   = $values[$themeKey] ?? $mode;
                $cssKey    = "appearance.{$sec}_{$mode}_custom_css";
                ?>
                <div class="mode-panel" id="panel-<?= esc($sec) ?>-<?= esc($mode) ?>" <?= $mi !== 0 ? 'hidden' : '' ?>>

                    <!-- Theme picker -->
                    <div class="mb-6">
                        <p class="text-xs opacity-40 mb-2">
                            <?= $mode === 'light'
                                ? __('settings.theme_light_hint', 'Select the theme for light mode.')
                                : __('settings.theme_dark_hint',  'Select the theme for dark mode.') ?>
                        </p>
                        <?= render('Settings::admin/_theme_picker', compact('fieldName', 'current', 'pickerId', 'mode')) ?>
                    </div>

                    <!-- Custom CSS -->
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-semibold opacity-70"><?= __('settings.custom_css', 'Custom CSS') ?></span>
                            <a href="https://daisyui.com/theme-generator/" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 text-xs bg-base-300 hover:bg-primary hover:text-primary-content
                                      px-2.5 py-1 rounded-md font-medium transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                                <?= __('settings.theme_generator', 'Theme Generator') ?>
                            </a>
                        </div>
                        <textarea
                            name="<?= esc(str_replace('.', '__', $cssKey)) ?>"
                            rows="7"
                            class="textarea textarea-bordered font-mono text-xs w-full"
                            placeholder="/* Paste generated @theme { ... } CSS here */"
                            spellcheck="false"><?= esc($values[$cssKey] ?? '') ?></textarea>
                        <p class="text-xs opacity-40 mt-1"><?= __('settings.custom_css_hint', 'Saved to a file, linked in &lt;head&gt;.') ?></p>
                    </div>

                </div>
                <?php endforeach; ?>

            </div>
            <?php endforeach; ?>

            <?php else: ?>
            <!-- ====== GENERIC GROUP UI ====== -->
            <div class="card bg-base-200 rounded-xl">
                <div class="card-body gap-y-4">
                    <?php foreach ($fields as $field): ?>
                    <?php
                    $fName  = str_replace('.', '__', $field->key);
                    $fValue = $values[$field->key] ?? '';
                    $fOpts  = $field->options ? json_decode($field->options, true) : [];
                    ?>
                    <?php
                    $fLabel = __($field->key, $field->label ?? $field->key);
                    $fHint  = __($field->key . '_hint', '');
                    $isMultiline = in_array($field->type, ['textarea', 'code'], true);
                    ?>
                    <div class="grid grid-cols-[12rem_1fr] <?= $isMultiline ? 'items-start' : 'items-center' ?> gap-x-6">
                        <span class="text-sm opacity-60<?= $isMultiline ? ' pt-1' : '' ?>"><?= esc($fLabel) ?></span>

                        <?php if ($field->type === 'textarea'): ?>
                        <div>
                        <textarea name="<?= esc($fName) ?>" rows="4" class="textarea textarea-bordered w-full"><?= esc($fValue) ?></textarea>
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>

                        <?php elseif ($field->type === 'code'): ?>
                        <div>
                        <textarea name="<?= esc($fName) ?>" rows="6" class="textarea textarea-bordered font-mono text-xs w-full" spellcheck="false"><?= esc($fValue) ?></textarea>
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>

                        <?php elseif ($field->type === 'select'): ?>
                        <div>
                        <select name="<?= esc($fName) ?>" class="select select-bordered select-sm w-full">
                            <?php foreach ($fOpts as $opt): ?>
                            <option value="<?= esc($opt['value']) ?>" <?= $fValue === $opt['value'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>

                        <?php elseif ($field->type === 'multiselect'): ?>
                        <div>
                        <select name="<?= esc($fName) ?>[]" multiple class="select select-bordered select-sm w-full h-32">
                            <?php
                            $selected = json_decode($fValue, true) ?? [];
                            foreach ($fOpts as $opt):
                            ?>
                            <option value="<?= esc($opt['value']) ?>" <?= in_array($opt['value'], $selected, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>

                        <?php elseif ($field->type === 'color'): ?>
                        <div>
                        <div class="flex items-center gap-2">
                            <input type="color" name="<?= esc($fName) ?>" value="<?= esc($fValue ?: '#000000') ?>" class="input w-10 h-9 p-1 cursor-pointer">
                            <input type="text" value="<?= esc($fValue) ?>" class="input input-bordered input-sm w-28 font-mono" readonly>
                        </div>
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>

                        <?php elseif ($field->type === 'url'): ?>
                        <div>
                        <input type="url" name="<?= esc($fName) ?>" value="<?= esc($fValue) ?>" class="input input-bordered input-sm w-full" placeholder="https://">
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>

                        <?php elseif ($field->type === 'password'): ?>
                        <div>
                        <div class="relative w-full">
                            <input type="password" name="<?= esc($fName) ?>" value="<?= esc($fValue) ?>" class="input input-bordered input-sm w-full font-mono pr-16">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-1 gap-0.5">
                                <button type="button" title="Show / Hide"
                                        class="btn btn-ghost btn-xs btn-square opacity-40 hover:opacity-80 js-toggle-password">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                                <button type="button" title="Copy"
                                        class="btn btn-ghost btn-xs btn-square opacity-40 hover:opacity-80 js-copy-value">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 icon-copy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 icon-check hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>

                        <?php else: ?>
                        <div>
                        <input type="text" name="<?= esc($fName) ?>" value="<?= esc($fValue) ?>" class="input input-bordered input-sm w-full">
                        <?php if ($fHint): ?><p class="text-xs opacity-40 mt-1"><?= esc($fHint) ?></p><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex justify-end mt-6">
                <button type="submit" class="btn btn-primary px-8"><?= __('settings.save', 'Save Settings') ?></button>
            </div>
        </form>
    </div>
</div>
</div>

<script>document.documentElement.dataset.liveTheme='1';document.body.dataset.liveTheme='1';</script>
<script src="<?= base_url('assets/settings/settings.js') ?>"></script>
