<div class="py-8 px-4 max-w-5xl mx-auto">
    <header class="mb-8">
        <h1 class="text-3xl font-bold"><?= esc($tag->title) ?></h1>
    </header>

    <?php if (empty($tag->items)): ?>
    <p class="opacity-40">No items with this tag.</p>
    <?php else: ?>
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($tag->items as $item): ?>
        <article class="card bg-base-200 shadow hover:shadow-md transition-shadow">
            <div class="card-body">
                <?php if ($item->image): ?><img src="<?= esc($item->image) ?>" alt="<?= esc($item->title) ?>" class="rounded mb-2 w-full object-cover h-36"><?php endif ?>
                <h2 class="card-title text-base">
                    <a href="/<?= esc($item->slug) ?>" class="hover:opacity-70"><?= esc($item->title) ?></a>
                </h2>
                <?php if ($item->meta_description): ?><p class="text-sm opacity-60"><?= esc($item->meta_description) ?></p><?php endif ?>
            </div>
        </article>
        <?php endforeach ?>
    </div>
    <?php endif ?>
</div>
