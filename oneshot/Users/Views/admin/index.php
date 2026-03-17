<div class="rounded-lg border border-base-300 overflow-hidden">
    <table class="table table-sm w-full">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('users.name', 'Name') ?></th>
                <th class="font-semibold"><?= __('users.email', 'Email') ?></th>
                <th class="font-semibold"><?= __('users.role', 'Role') ?></th>
                <th class="font-semibold"><?= __('users.status', 'Status') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($users as $user): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td>
                    <a href="<?= base_url(config('Prefixes')->admin . '/users/' . signId($user->id)) ?>" class="font-medium hover:opacity-70 transition-opacity">
                        <?= esc($user->name ?? '—') ?>
                    </a>
                </td>
                <td class="opacity-60 text-sm"><?= esc($user->email) ?></td>
                <td><span class="badge badge-ghost badge-sm"><?= esc($user->role) ?></span></td>
                <td><span class="badge badge-sm <?= $user->status === 'active' ? 'badge-success' : 'badge-error' ?>"><?= esc($user->status) ?></span></td>
                <td>
                    <div class="flex items-center justify-end">
                        <div class="tooltip tooltip-left" data-tip="<?= __('users.view', 'View') ?>">
                            <a href="<?= base_url(config('Prefixes')->admin . '/users/' . signId($user->id)) ?>" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="5" class="py-12 text-center text-sm opacity-40">—</td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
