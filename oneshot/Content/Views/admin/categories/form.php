<?php
$slugWarning = session()->getFlashdata('content_slug_warning');
if ($slugWarning): ?>
<div class="alert alert-warning mb-4"><?= esc($slugWarning) ?></div>
<?php endif ?>

<div class="max-w-3xl">
<form method="post" action="<?= $category ? route_to('admin.content.categories.edit', signId($category->id)) : route_to('admin.content.categories.create') ?>">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.parent', 'Parent') ?></span>
                <select name="parent_id" class="select select-sm select-bordered">
                    <option value=""><?= __('content.root_category', '— Root —') ?></option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c->id ?>" <?= ($category && (int)$category->parent_id === (int)$c->id) ? 'selected' : '' ?>>
                        <?= esc($c->title) ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.title', 'Title') ?></span>
                <input type="text" name="title" id="title" value="<?= esc($category->title ?? '') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.slug', 'Slug') ?></span>
                <input type="text" name="slug" id="slug" value="<?= esc($category->slug ?? '') ?>" class="input input-sm input-bordered font-mono">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.image', 'Image URL') ?></span>
                <input type="text" name="image" value="<?= esc($category->image ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.meta_title', 'Meta title') ?></span>
                <input type="text" name="meta_title" value="<?= esc($category->meta_title ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('content.meta_description', 'Meta description') ?></span>
                <textarea name="meta_description" rows="2" class="textarea textarea-sm textarea-bordered"><?= esc($category->meta_description ?? '') ?></textarea>
            </div>

        </div>
    </div>

    <!-- Content editor -->
    <div class="card bg-white shadow mt-4">
        <div class="card-body p-4 sm:p-6">
            <p class="text-xs font-semibold uppercase opacity-40 tracking-wider mb-3"><?= __('content.content', 'Content') ?></p>
            <input type="hidden" name="content" id="content-data" value="<?= esc($category->content ?? '') ?>">
            <div id="editorjs" class="content-body min-h-[200px]"
                 data-editor="content-data"
                 data-upload-url="<?= route_to('admin.content.upload.image') ?>"
                 data-fetch-url="<?= route_to('admin.content.fetch.url') ?>"
                 data-csrf="<?= csrf_token() ?>"
                 data-csrf-value="<?= csrf_hash() ?>"></div>
        </div>
    </div>

    <!-- Extra fields -->
    <div class="card bg-base-200 shadow mt-4">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.template', 'Template') ?></span>
                <select name="template" class="select select-sm select-bordered">
                    <option value="category" <?= ($category->template ?? 'category') === 'category' ? 'selected' : '' ?>>category</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.sort', 'Sort') ?></span>
                <input type="number" name="sort" value="<?= (int)($category->sort ?? 0) ?>" class="input input-sm input-bordered w-24">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.is_active', 'Active') ?></span>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" <?= !isset($category) || $category->is_active ? 'checked' : '' ?>>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= __('content.save', 'Save') ?></button>
                <a href="<?= route_to('admin.content.categories') ?>" class="btn btn-ghost btn-sm ml-2"><?= __('content.cancel', 'Cancel') ?></a>
            </div>

        </div>
    </div>
</form>
</div>

<script>
(function () {
    const title = document.getElementById('title');
    const slug  = document.getElementById('slug');
    let manual  = slug.value !== '';

    slug.addEventListener('input', () => { manual = true; });

    title.addEventListener('input', () => {
        if (manual) return;
        slug.value = title.value
            .toLowerCase()
            .replace(/[\s_]+/g, '-')
            .replace(/[^a-z0-9\-]/g, '')
            .replace(/-{2,}/g, '-')
            .replace(/^-|-$/g, '');
    });
})();
</script>
