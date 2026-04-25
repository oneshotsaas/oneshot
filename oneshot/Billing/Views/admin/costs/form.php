<div class="max-w-2xl">
<form method="post">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.action_name', 'Action') ?> <span class="opacity-50 text-xs">e.g. image.generate.dall-e-3</span></span>
                <input type="text" name="action" value="<?= esc($cost->action ?? '') ?>" class="input input-sm input-bordered font-mono" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.label', 'Label') ?></span>
                <input type="text" name="label" value="<?= esc($cost->label ?? '') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.unit_type', 'Unit Type') ?></span>
                <select name="unit_type" id="unit_type" class="select select-sm select-bordered" onchange="updateMetaHint()">
                    <?php foreach (['unit','second','token'] as $ut): ?>
                    <option value="<?= $ut ?>" <?= ($cost->unit_type ?? 'unit') === $ut ? 'selected' : '' ?>><?= ucfirst($ut) ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.cost_per_unit', 'Cost per Unit') ?></span>
                <input type="text" name="cost_per_unit" value="<?= esc($cost->cost_per_unit ?? '1.0000') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1">
                    <?= __('billing.meta', 'Meta (JSON)') ?>
                    <a href="#" onclick="insertMetaExample(event)" class="link link-primary text-xs block mt-1">example</a>
                    <span id="meta-hint" class="opacity-40 text-xs block mt-1"></span>
                </span>
                <textarea name="meta" id="meta" rows="8" class="textarea textarea-sm textarea-bordered font-mono"><?= esc($cost->meta ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.active', 'Active') ?></span>
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" <?= !isset($cost->is_active) || $cost->is_active ? 'checked' : '' ?>>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Save') ?></button>
            </div>

        </div>
    </div>
</form>
</div>

<script>
var metaExamples = {
    unit:   '{\n  "param": "size",\n  "variants": {\n    "512x512":   {"type": "multiply", "value": 1},\n    "1024x1024": {"type": "multiply", "value": 2}\n  }\n}',
    second: '{\n  "param": "seconds",\n  "variants": {\n    "audio": {"type": "multiply", "value": 1.5},\n    "4k":    {"type": "multiply", "value": 2.0}\n  }\n}',
    token:  '{\n  "batch": 1000,\n  "output_multiplier": 3,\n  "cached_discount": 0.5\n}'
};
var metaHints = {
    unit:   'param: key in params. variants: multiply or fixed',
    second: 'param: seconds key. variants: boolean params → multiply',
    token:  'batch size, output_multiplier, cached_discount (0–1)'
};
function insertMetaExample(e) {
    e.preventDefault();
    document.getElementById('meta').value = metaExamples[document.getElementById('unit_type').value] || '';
}
function updateMetaHint() {
    document.getElementById('meta-hint').textContent = metaHints[document.getElementById('unit_type').value] || '';
}
updateMetaHint();
</script>
