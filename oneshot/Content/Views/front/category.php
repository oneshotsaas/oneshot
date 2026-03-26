<?php helper('content') ?>
<?php $chain = content_category_chain((int)$category->id) ?>
<div class="py-10 px-4 max-w-5xl mx-auto">

    <header class="mb-8">
        <?php if (count($chain) > 1): ?>
        <nav class="content-breadcrumb">
            <?php foreach (array_slice($chain, 0, -1) as $bc): ?>
            <a href="<?= content_url($bc, 'category') ?>"><?= esc($bc->title) ?></a>
            <span class="content-breadcrumb-sep">/</span>
            <?php endforeach ?>
            <span><?= esc($category->title) ?></span>
        </nav>
        <?php endif ?>
        <?php if ($category->image): ?>
        <img src="<?= esc($category->image) ?>" alt="<?= esc($category->title) ?>" class="rounded-lg mb-4 w-full object-cover max-h-64">
        <?php endif ?>
        <h1 class="text-3xl font-bold"><?= esc($category->title) ?></h1>
    </header>

    <?php if (!empty($category->content)): ?>
    <div class="content-body mb-8">
        <?= editorjs_render($category->content) ?>
    </div>
    <?php endif ?>

    <?php if (!empty($category->subcategories)): ?>
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 mb-8">
        <?php foreach ($category->subcategories as $sub): ?>
        <a href="<?= content_url($sub, 'category') ?>"
           class="rounded-xl border border-base-200 bg-base-100 hover:border-base-300 hover:shadow-sm transition-all p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-base-200 flex items-center justify-center flex-shrink-0 text-sm font-bold opacity-50">
                <?= mb_substr(esc($sub->title), 0, 1) ?>
            </div>
            <span class="font-semibold text-sm"><?= esc($sub->title) ?></span>
        </a>
        <?php endforeach ?>
    </div>
    <?php endif ?>

    <?php if (empty($category->items)): ?>
    <?php if (empty($category->subcategories)): ?>
    <div class="content-empty">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        <p class="text-sm"><?= __('content.no_items', 'No posts yet.') ?></p>
    </div>
    <?php endif ?>
    <?php else: ?>
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($category->items as $item): ?>
        <article class="rounded-xl border border-base-200 bg-base-100 hover:border-base-300 hover:shadow-sm transition-all">
            <?php if ($item->image): ?>
            <a href="<?= content_url($item, 'item') ?>">
                <img src="<?= esc($item->image) ?>" alt="<?= esc($item->title) ?>" class="rounded-t-xl w-full object-cover h-36">
            </a>
            <?php endif ?>
            <div class="p-4 flex flex-col gap-2">
                <h2 class="font-semibold text-base leading-snug">
                    <a href="<?= content_url($item, 'item') ?>" class="hover:opacity-70 transition-opacity"><?= esc($item->title) ?></a>
                </h2>
                <?php if ($item->meta_description): ?>
                <p class="text-sm opacity-55 leading-snug"><?= esc($item->meta_description) ?></p>
                <?php endif ?>
                <?php if ($item->created_at): ?>
                <p class="text-xs opacity-35 mt-auto pt-1"><?= date('M j, Y', strtotime($item->created_at)) ?></p>
                <?php endif ?>
            </div>
        </article>
        <?php endforeach ?>
    </div>
    <?php endif ?>
</div>
