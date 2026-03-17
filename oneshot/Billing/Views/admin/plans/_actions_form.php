<?php if (!empty($plan->id)): ?>
<a href="<?= route_to('admin.billing.plan.prices', signId($plan->id)) ?>" class="btn btn-ghost btn-sm"><?= __('billing.manage', 'Manage') ?> <?= __('billing.prices', 'Prices') ?></a>
<?php endif ?>
<a href="<?= route_to('admin.billing.plans') ?>" class="btn btn-ghost btn-sm"><?= __('billing.back', 'Back') ?></a>
