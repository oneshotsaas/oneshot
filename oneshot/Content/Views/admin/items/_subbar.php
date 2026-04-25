<?php
$base = route_to('admin.content.items');
$url  = function(string $type = '', int $cat = 0) use ($base): string {
    $q = array_filter(['type' => $type, 'cat' => $cat ?: '']);
    return $base . ($q ? '?' . http_build_query($q) : '');
};
$ft = $filterType ?? '';
$fc = $filterCat  ?? 0;
?>
<div id="items-filters-m" class="hidden sm:hidden sticky top-14 z-20 px-4 py-2 bg-base-100 border-b border-base-200 flex-wrap items-center gap-1">
    <a href="<?= $url('', $fc) ?>" class="btn btn-xs gap-1 <?= $ft === '' ? 'btn-neutral' : 'btn-ghost opacity-60' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
        <?= __('content.filter_all', 'All') ?>
    </a>
    <a href="<?= $url('post', $fc) ?>" class="btn btn-xs gap-1 <?= $ft === 'post' ? 'btn-neutral' : 'btn-ghost opacity-60' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
        <?= __('content.type_post', 'Posts') ?>
    </a>
    <a href="<?= $url('page', $fc) ?>" class="btn btn-xs gap-1 <?= $ft === 'page' ? 'btn-neutral' : 'btn-ghost opacity-60' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <?= __('content.type_page', 'Pages') ?>
    </a>
    <?php if (!empty($categories)): ?>
    <select class="select select-xs select-bordered flex-1" onchange="window.location=this.value">
        <option value="<?= $url($ft, 0) ?>" <?= !$fc ? 'selected' : '' ?>><?= __('content.filter_category', 'All categories') ?></option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $url($ft, (int)$c->id) ?>" <?= $fc === (int)$c->id ? 'selected' : '' ?>>
            <?= str_repeat('·  ', $c->depth) ?><?= esc($c->title) ?>
        </option>
        <?php endforeach ?>
    </select>
    <?php endif ?>
</div>
