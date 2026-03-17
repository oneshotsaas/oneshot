<form method="get" class="flex items-center gap-2">
    <select name="action" class="select select-bordered select-sm w-40">
        <option value=""><?= __('billing.all_actions', 'All Actions') ?></option>
        <option value="image." <?= ($filters['action'] ?? '') === 'image.' ? 'selected' : '' ?>><?= __('billing.action_image', 'Image') ?></option>
        <option value="video." <?= ($filters['action'] ?? '') === 'video.' ? 'selected' : '' ?>><?= __('billing.action_video', 'Video') ?></option>
        <option value="llm."   <?= ($filters['action'] ?? '') === 'llm.'   ? 'selected' : '' ?>><?= __('billing.action_llm', 'LLM') ?></option>
    </select>
    <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="input input-bordered input-sm w-36">
    <input type="date" name="date_to"   value="<?= esc($filters['date_to']   ?? '') ?>" class="input input-bordered input-sm w-36">
    <button class="btn btn-sm btn-primary"><?= __('billing.filter', 'Filter') ?></button>
    <a href="<?= route_to('billing.usage') ?>" class="btn btn-sm btn-ghost"><?= __('billing.reset', 'Reset') ?></a>
</form>
