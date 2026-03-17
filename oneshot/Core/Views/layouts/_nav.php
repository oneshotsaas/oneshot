<?php
/** Vars: $navItems (array from config('Nav')->admin / ->app) */
$url = current_url();
?>
<ul class="menu menu-sm px-2 gap-0.5">
<?php foreach ($navItems as $item): ?>

    <?php if (isset($item['divider'])): ?>
    <li class="menu-title text-xs opacity-40 pt-2"><?= esc($item['divider']) ?></li>

    <?php else:
        $href   = route_to($item['route']);
        $match  = $item['match'] ?? $href;
        $active = str_contains($url, $match);
    ?>
    <li>
        <a href="<?= esc($href) ?>" class="<?= $active ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?= esc($item['icon']) ?>"/>
            </svg>
            <?= esc($item['label']) ?>
        </a>
    </li>
    <?php endif; ?>

<?php endforeach; ?>
</ul>
