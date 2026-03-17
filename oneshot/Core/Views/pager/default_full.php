<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);

if (! $pager->hasPreviousPage() && ! $pager->hasNextPage()) {
    return;
}
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>" class="flex items-center justify-center mt-4">
    <div class="join">
        <?php if ($pager->hasPreviousPage()): ?>
            <?php $first = preg_replace('#[\?\&]page=(\d+)#si', '', $pager->getFirst()) ?>
            <a href="<?= $first ?>" aria-label="<?= lang('Pager.first') ?>" class="join-item btn btn-sm btn-ghost">«</a>
            <a href="<?= $pager->getPreviousPage() ?>" aria-label="<?= lang('Pager.previous') ?>" class="join-item btn btn-sm btn-ghost">‹</a>
        <?php endif ?>

        <?php foreach ($pager->links() as $link): ?>
        <a href="<?= $link['uri'] ?>" class="join-item btn btn-sm <?= $link['active'] ? 'btn-active pointer-events-none' : 'btn-ghost' ?>">
            <?= $link['title'] ?>
        </a>
        <?php endforeach ?>

        <?php if ($pager->hasNextPage()): ?>
            <?php preg_match('#page=(\d+)#si', $pager->getLast(), $m) ?>
            <a href="<?= $pager->getNextPage() ?>" aria-label="<?= lang('Pager.next') ?>" class="join-item btn btn-sm btn-ghost">›</a>
            <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>" class="join-item btn btn-sm btn-ghost">»</a>
        <?php endif ?>
    </div>
</nav>
