<?php
helper('content');
$toc   = toc_extract($item->content ?? '[]');
$catId = (int)($item->canonical_category_id ?? 0);
if (!$catId && !empty($item->categories)) {
    $catId = (int)$item->categories[0]->id;
}
$chain = $catId ? content_category_chain($catId) : [];
?>
<div class="grid grid-cols-1 lg:grid-cols-[15rem_1fr_15rem] gap-8 py-10 px-6 max-w-7xl mx-auto">

    <!-- TOC -->
    <aside class="hidden lg:block">
        <?php if (!empty($toc)): ?>
        <div class="sticky top-20 pl-1">
            <p class="text-xs font-semibold uppercase tracking-wider opacity-40 mb-3">Contents</p>
            <nav class="flex flex-col gap-1">
                <?php foreach ($toc as $h): ?>
                <a href="#<?= esc($h['anchor']) ?>"
                   class="text-sm opacity-60 hover:opacity-100 transition-opacity"
                   style="padding-left:<?= ($h['level'] - 2) * 0.75 ?>rem">
                    <?= esc($h['text']) ?>
                </a>
                <?php endforeach ?>
            </nav>
        </div>
        <?php endif ?>
    </aside>

    <!-- Content -->
    <article class="content-body min-w-0">

        <?php if (!empty($chain)): ?>
        <nav class="content-breadcrumb">
            <?php foreach ($chain as $bc): ?>
            <a href="<?= content_url($bc, 'category') ?>"><?= esc($bc->title) ?></a>
            <span class="content-breadcrumb-sep">/</span>
            <?php endforeach ?>
            <span><?= esc($item->title) ?></span>
        </nav>
        <?php endif ?>

        <h1><?= esc($item->title) ?></h1>
        <?php if ($item->created_at): ?>
        <p class="text-sm opacity-40 -mt-2 mb-6"><?= date('F j, Y', strtotime($item->created_at)) ?></p>
        <?php endif ?>
        <?php if ($item->image): ?>
        <img src="<?= esc($item->image) ?>" alt="<?= esc($item->title) ?>" class="w-full rounded-xl object-cover max-h-72 mb-8">
        <?php endif ?>
        <?= editorjs_render($item->content ?? '[]') ?>
    </article>

    <!-- Sidebar: categories + tags -->
    <aside class="hidden lg:flex flex-col gap-6">
        <?php if (!empty($item->categories)): ?>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider opacity-40 mb-2">Categories</p>
            <div class="flex flex-wrap gap-1">
                <?php foreach ($item->categories as $cat): ?>
                <a href="<?= content_url($cat, 'category') ?>"
                   class="badge badge-sm border-0 bg-base-200 hover:bg-base-300 transition-colors"><?= esc($cat->title) ?></a>
                <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>
        <?php if (!empty($item->tags)): ?>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider opacity-40 mb-2">Tags</p>
            <div class="flex flex-wrap gap-1">
                <?php foreach ($item->tags as $tag): ?>
                <a href="<?= content_url($tag, 'tag') ?>"
                   class="badge badge-sm border border-base-300 bg-transparent hover:bg-base-200 transition-colors"><?= esc($tag->title) ?></a>
                <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>
    </aside>

</div>
