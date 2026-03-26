<?php helper('content') ?>
<?php
$catId = (int)($item->canonical_category_id ?? 0);
$chain = $catId ? content_category_chain($catId) : [];
?>
<div class="py-10 px-4 max-w-3xl mx-auto">

    <?php if (!empty($chain)): ?>
    <nav class="content-breadcrumb">
        <?php foreach ($chain as $bc): ?>
        <a href="<?= content_url($bc, 'category') ?>"><?= esc($bc->title) ?></a>
        <span class="content-breadcrumb-sep">/</span>
        <?php endforeach ?>
        <span><?= esc($item->title) ?></span>
    </nav>
    <?php endif ?>

    <article class="content-body">
        <h1><?= esc($item->title) ?></h1>
        <?= editorjs_render($item->content ?? '[]') ?>
    </article>

</div>
