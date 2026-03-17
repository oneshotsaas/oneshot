<?php if (!empty($breadcrumbs)): ?>
<div class="breadcrumbs text-sm px-4 py-2">
    <ul>
        <?php foreach ($breadcrumbs as $i => $bc): ?>
        <?php $isLast = ($i === count($breadcrumbs) - 1); ?>
        <li>
            <?php if (!$isLast && !empty($bc['url'])): ?>
            <a href="<?= esc($bc['url']) ?>"><?= esc($bc['label']) ?></a>
            <?php else: ?>
            <?= esc($bc['label']) ?>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
