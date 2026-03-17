<div class="max-w-lg">
<div class="card bg-base-200 shadow">
        <div class="card-body gap-3">
            <div>
                <span class="text-sm opacity-60"><?= __('users.name', 'Name') ?></span>
                <p class="font-medium"><?= esc($user->name ?? '—') ?></p>
            </div>
            <div>
                <span class="text-sm opacity-60"><?= __('users.email', 'Email') ?></span>
                <p class="font-medium"><?= esc($user->email) ?></p>
            </div>
            <div>
                <span class="text-sm opacity-60"><?= __('users.role', 'Role') ?></span>
                <p class="font-medium"><?= esc($user->role) ?></p>
            </div>
            <div>
                <span class="text-sm opacity-60"><?= __('users.status', 'Status') ?></span>
                <p><span class="badge <?= $user->status === 'active' ? 'badge-success' : 'badge-error' ?>"><?= esc($user->status) ?></span></p>
            </div>
        </div>
    </div>
</div>
