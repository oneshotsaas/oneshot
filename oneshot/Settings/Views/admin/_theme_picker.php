<?php
/**
 * Theme Picker Partial
 *
 * Required vars:
 *   $fieldName  — hidden input name  e.g. "appearance__admin_dark_theme"
 *   $current    — currently selected theme name
 *   $pickerId   — unique ID for this picker instance
 *   $mode       — 'light' or 'dark' — filters which themes are shown
 */

$lightThemes = [
    'light','cupcake','bumblebee','emerald','corporate','retro',
    'valentine','garden','lofi','pastel','fantasy',
    'wireframe','cmyk','autumn','lemonade','winter','cyberpunk','nord','acid',
];
$darkThemes = [
    'dark','synthwave','halloween','black','luxury',
    'dracula','business','night','coffee','dim','sunset','forest','aqua',
];

$themes = ($mode ?? 'light') === 'dark' ? $darkThemes : $lightThemes;

// Fallback: if saved value is not in the filtered list, prepend it
if ($current && !in_array($current, $themes, true)) {
    array_unshift($themes, $current);
}
?>

<div class="theme-picker"
     id="picker-<?= esc($pickerId) ?>"
     data-picker-id="<?= esc($pickerId) ?>"
     data-saved-theme="<?= esc($current) ?>">

    <input type="hidden" name="<?= esc($fieldName) ?>" value="<?= esc($current) ?>" id="input-<?= esc($pickerId) ?>">

    <!-- Theme grid -->
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7 gap-2">
        <?php foreach ($themes as $theme): ?>
        <?php $isActive = $theme === $current; ?>
        <button type="button"
                data-picker="<?= esc($pickerId) ?>"
                data-theme-name="<?= esc($theme) ?>"
                class="theme-card relative rounded-xl border-2 overflow-hidden cursor-pointer transition-all
                       <?= $isActive
                           ? 'border-primary border-2'
                           : 'border-base-300 hover:border-primary/60 hover:shadow-sm' ?>"
                title="<?= esc(ucfirst($theme)) ?>">

            <!-- Real DaisyUI swatch via scoped data-theme -->
            <div data-theme="<?= esc($theme) ?>" class="p-2 bg-base-100">
                <div class="flex gap-1 mb-1.5">
                    <span class="w-3 h-3 rounded-full bg-primary   shrink-0"></span>
                    <span class="w-3 h-3 rounded-full bg-secondary shrink-0"></span>
                    <span class="w-3 h-3 rounded-full bg-accent    shrink-0"></span>
                    <span class="w-3 h-3 rounded-full bg-neutral   shrink-0"></span>
                </div>
                <div class="h-1.5 rounded bg-base-200 w-full"></div>
                <div class="h-1   rounded bg-base-300 w-3/4 mt-0.5"></div>
            </div>
            <div data-theme="<?= esc($theme) ?>" class="bg-base-200 px-1 py-1 text-center">
                <span class="text-[10px] font-medium truncate block text-base-content"><?= esc(ucfirst($theme)) ?></span>
            </div>

            <!-- Checkmark for active -->
            <div class="theme-check absolute top-1 right-1 w-4 h-4 bg-primary rounded-full
                        flex items-center justify-center transition-opacity
                        <?= $isActive ? 'opacity-100' : 'opacity-0' ?>">
                <svg class="w-2.5 h-2.5 text-primary-content" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

        </button>
        <?php endforeach; ?>
    </div>

</div>
