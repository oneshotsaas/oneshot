<div id="usage-filters-m" class="hidden sm:hidden sticky top-14 z-20 px-4 py-2 bg-base-100 border-b border-base-200 flex-wrap items-center gap-2">
    <form method="get" class="flex flex-wrap items-center gap-2 w-full" data-loading>
        <select name="action_prefix" class="select select-bordered select-sm flex-1 min-w-[8rem]">
            <option value=""><?= __('billing.all_actions', 'All Actions') ?></option>
            <option value="image." <?= ($filters['action_prefix'] ?? '') === 'image.' ? 'selected' : '' ?>><?= __('billing.action_image', 'Image') ?></option>
            <option value="video." <?= ($filters['action_prefix'] ?? '') === 'video.' ? 'selected' : '' ?>><?= __('billing.action_video', 'Video') ?></option>
            <option value="llm."   <?= ($filters['action_prefix'] ?? '') === 'llm.'   ? 'selected' : '' ?>><?= __('billing.action_llm', 'LLM') ?></option>
        </select>
        <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="input input-bordered input-sm flex-1 min-w-[8rem]">
        <input type="date" name="date_to"   value="<?= esc($filters['date_to']   ?? '') ?>" class="input input-bordered input-sm flex-1 min-w-[8rem]">
        <button class="btn btn-sm btn-primary"><?= __('billing.filter', 'Filter') ?></button>
        <a href="<?= route_to('billing.usage') ?>" class="btn btn-sm btn-ghost"><?= __('billing.reset', 'Reset') ?></a>
    </form>
</div>
