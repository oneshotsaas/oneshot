<?php
helper('content');
$catFlat      = category_flat($categories);
$selectedCats = array_map('intval', $item->category_ids ?? []);
$selectedTags = array_map('intval', $item->tag_ids ?? []);
$currentType  = $item->type ?? 'post';
$action = !empty($item->id)
    ? route_to('admin.content.items.edit', signId($item->id))
    : route_to('admin.content.items.create');
?>
<form method="post" action="<?= $action ?>" id="item-form">
    <?= csrf_field() ?>
    <input type="hidden" name="template" id="item-template" value="<?= esc($item->template ?? $currentType) ?>">
    <input type="hidden" name="_back" value="<?= esc($back ?? '') ?>">

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_22rem] gap-6">

        <!-- Main column -->
        <div class="flex flex-col gap-4">

            <!-- Title + Slug -->
            <div class="card bg-base-200 shadow">
                <div class="card-body gap-3">
                    <input type="text" name="title" id="title" value="<?= esc($item->title ?? '') ?>"
                           placeholder="<?= __('content.title', 'Title') ?>"
                           class="input input-bordered w-full text-lg font-semibold" required>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="opacity-40 shrink-0"><?= __('content.slug', 'Slug') ?>:</span>
                        <input type="text" name="slug" id="slug" value="<?= esc($item->slug ?? '') ?>"
                               class="input input-xs input-bordered font-mono w-full" placeholder="auto">
                    </div>
                </div>
            </div>

            <!-- Editor.js -->
            <div class="card shadow bg-white">
                <div class="card-body p-6">
                    <input type="hidden" name="content" id="content-data" value="<?= esc($item->content ?? '') ?>">
                    <div id="editorjs" class="content-body min-h-[400px]"
                         data-editor="content-data"
                         data-upload-url="<?= route_to('admin.content.upload.image') ?>"
                         data-upload-file-url="<?= route_to('admin.content.upload.file') ?>"
                         data-fetch-url="<?= route_to('admin.content.fetch.url') ?>"
                         data-csrf="<?= csrf_token() ?>"
                         data-csrf-value="<?= csrf_hash() ?>"></div>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="flex flex-col gap-4">

            <!-- Publish -->
            <div class="card bg-base-200 shadow">
                <div class="card-body gap-4">
                    <div class="grid grid-cols-[8rem_1fr] items-center gap-x-4">
                        <span class="text-sm opacity-60"><?= __('content.type', 'Type') ?></span>
                        <select name="type" id="item-type" class="select select-sm select-bordered">
                            <option value="post" <?= $currentType === 'post' ? 'selected' : '' ?>><?= __('content.type_post', 'Post') ?></option>
                            <option value="page" <?= $currentType === 'page' ? 'selected' : '' ?>><?= __('content.type_page', 'Page') ?></option>
                        </select>
                    </div>
                    <div class="grid grid-cols-[8rem_1fr] items-center gap-x-4">
                        <span class="text-sm opacity-60"><?= __('content.is_active', 'Active') ?></span>
                        <div>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm"
                                   <?= empty($item->id) || $item->is_active ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-full"><?= __('content.save', 'Save') ?></button>
                </div>
            </div>

            <!-- SEO -->
            <div class="card bg-base-200 shadow">
                <div class="card-body gap-3">
                    <p class="text-xs font-semibold uppercase opacity-40 tracking-wider"><?= __('content.seo', 'SEO') ?></p>
                    <input type="text" name="image" value="<?= esc($item->image ?? '') ?>"
                           placeholder="<?= __('content.image', 'Image URL') ?>" class="input input-sm input-bordered w-full">
                    <input type="text" name="meta_title" value="<?= esc($item->meta_title ?? '') ?>"
                           placeholder="<?= __('content.meta_title', 'Meta title') ?>" class="input input-sm input-bordered w-full">
                    <textarea name="meta_description" rows="2"
                              placeholder="<?= __('content.meta_description', 'Meta description') ?>"
                              class="textarea textarea-sm textarea-bordered w-full"><?= esc($item->meta_description ?? '') ?></textarea>
                </div>
            </div>

            <!-- Category -->
            <div class="card bg-base-200 shadow">
                <div class="card-body gap-3">
                    <p class="text-xs font-semibold uppercase opacity-40 tracking-wider"><?= __('content.categories', 'Category') ?></p>
                    <select name="canonical_category_id" id="canonical-select" class="select select-sm select-bordered w-full">
                        <option value=""><?= __('content.canonical_none', '— None —') ?></option>
                        <?php foreach ($catFlat as $c): ?>
                        <option value="<?= $c->id ?>"
                                <?= (isset($item->canonical_category_id) && (int)$item->canonical_category_id === (int)$c->id) ? 'selected' : '' ?>>
                            <?php for ($d = 0; $d < $c->depth; $d++): ?>&#8194;<?php endfor ?>
                            <?= esc($c->title) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                    <div id="extra-cats-wrap" style="display:none" class="flex flex-col gap-1 max-h-48 overflow-y-auto border-t border-base-300 pt-2">
                        <?php foreach ($catFlat as $c): ?>
                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:opacity-80">
                            <input type="checkbox" name="category_ids[]" value="<?= $c->id ?>"
                                   class="checkbox checkbox-xs cat-checkbox"
                                   <?= in_array((int)$c->id, $selectedCats) ? 'checked' : '' ?>>
                            <span style="padding-left:<?= $c->depth ?>rem"><?= esc($c->title) ?></span>
                        </label>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <div class="card bg-base-200 shadow">
                <div class="card-body gap-2">
                    <p class="text-xs font-semibold uppercase opacity-40 tracking-wider"><?= __('content.tags', 'Tags') ?></p>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 max-h-48 overflow-y-auto">
                        <?php foreach ($tags as $tag): ?>
                        <label class="flex items-center gap-1 text-sm cursor-pointer hover:opacity-80">
                            <input type="checkbox" name="tag_ids[]" value="<?= $tag->id ?>"
                                   class="checkbox checkbox-xs"
                                   <?= in_array((int)$tag->id, $selectedTags) ? 'checked' : '' ?>>
                            <?= esc($tag->title) ?>
                        </label>
                        <?php endforeach ?>
                        <?php if (empty($tags)): ?>
                        <p class="text-xs opacity-40"><?= __('content.no_tags_avail', 'No tags') ?></p>
                        <?php endif ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
// Slug auto-gen
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

// Template hidden field follows type
(function () {
    const typeEl = document.getElementById('item-type');
    const tmplEl = document.getElementById('item-template');
    typeEl.addEventListener('change', () => { tmplEl.value = typeEl.value; });
})();

// Primary category ↔ additional categories sync
(function () {
    const primarySelect = document.getElementById('canonical-select');
    const extraWrap     = document.getElementById('extra-cats-wrap');
    function syncPrimary() {
        const primaryId = primarySelect.value;

        // Show/hide additional categories panel
        extraWrap.style.display = primaryId ? '' : 'none';

        if (!primaryId) {
            // No primary → uncheck all additional
            extraWrap.querySelectorAll('.cat-checkbox').forEach(cb => {
                cb.checked  = false;
                cb.disabled = false;
            });
            return;
        }

        // Ensure primary is always checked (readonly via click prevention)
        extraWrap.querySelectorAll('.cat-checkbox').forEach(cb => {
            if (cb.value === primaryId) {
                cb.checked = true;
                cb.dataset.locked = '1';
                cb.closest('label').style.opacity = '0.5';
                cb.closest('label').style.cursor  = 'default';
            } else {
                delete cb.dataset.locked;
                cb.closest('label').style.opacity = '';
                cb.closest('label').style.cursor  = '';
            }
        });
    }

    extraWrap.addEventListener('click', function (e) {
        if (e.target.classList.contains('cat-checkbox') && e.target.dataset.locked) {
            e.preventDefault();
        }
    });

    primarySelect.addEventListener('change', syncPrimary);
    syncPrimary();
})();
</script>
