<?php
$slugWarning = session()->getFlashdata('content_slug_warning');
if ($slugWarning): ?>
<div class="alert alert-warning mb-4"><?= esc($slugWarning) ?></div>
<?php endif ?>

<div class="max-w-2xl">
<form method="post" action="<?= $tag ? route_to('admin.content.tags.edit', signId($tag->id)) : route_to('admin.content.tags.create') ?>">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.title', 'Title') ?></span>
                <input type="text" name="title" id="title" value="<?= esc($tag->title ?? '') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.slug', 'Slug') ?></span>
                <input type="text" name="slug" id="slug" value="<?= esc($tag->slug ?? '') ?>" class="input input-sm input-bordered font-mono">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.image', 'Image URL') ?></span>
                <input type="text" name="image" value="<?= esc($tag->image ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.meta_title', 'Meta title') ?></span>
                <input type="text" name="meta_title" value="<?= esc($tag->meta_title ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('content.meta_description', 'Meta description') ?></span>
                <textarea name="meta_description" rows="2" class="textarea textarea-sm textarea-bordered"><?= esc($tag->meta_description ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.template', 'Template') ?></span>
                <input type="text" name="template" value="<?= esc($tag->template ?? 'tag') ?>" class="input input-sm input-bordered font-mono">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('content.is_active', 'Active') ?></span>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" <?= !isset($tag) || $tag->is_active ? 'checked' : '' ?>>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= __('content.save', 'Save') ?></button>
                <a href="<?= route_to('admin.content.tags') ?>" class="btn btn-ghost btn-sm ml-2"><?= __('content.cancel', 'Cancel') ?></a>
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
