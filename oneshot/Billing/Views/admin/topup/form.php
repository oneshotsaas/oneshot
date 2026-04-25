<div class="max-w-2xl">
<form method="post">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.balance', 'Current Balance') ?></span>
                <span class="text-xl font-bold"><?= number_format((float)$balance, 2) ?></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.credits_to_add', 'Credits') ?> <span class="opacity-50 text-xs">(negative=deduct)</span></span>
                <input type="number" name="credits" step="0.0001" value="" class="input input-sm input-bordered" required autofocus>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.note', 'Note') ?></span>
                <input type="text" name="description" value="" class="input input-sm input-bordered">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Apply') ?></button>
            </div>

        </div>
    </div>
</form>
</div>
