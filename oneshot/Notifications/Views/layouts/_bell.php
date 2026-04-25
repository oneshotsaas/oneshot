<details class="dropdown dropdown-top" id="notif-dropdown">
    <summary class="list-none appearance-none btn btn-ghost btn-xs btn-square relative tooltip tooltip-left cursor-pointer" data-tip="<?= __('notifications.notifications', 'Notifications') ?>" style="display:inline-flex">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.437L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span id="notif-badge" class="badge badge-error badge-xs absolute -top-1 -right-1 hidden">0</span>
    </summary>
    <div id="notif-panel" class="dropdown-content !fixed !left-0 !bottom-10 !w-64 z-[200] p-0 shadow-xl bg-base-100 border border-base-300 rounded-box overflow-hidden">
        <div class="p-3 border-b border-base-300 flex items-center justify-between">
            <span class="font-semibold text-sm"><?= __('notifications.notifications', 'Notifications') ?></span>
            <a href="<?= route_to('app.notifications') ?>" class="text-xs link link-primary">
                <?= __('notifications.see_all', 'See all') ?>
            </a>
        </div>
        <ul id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-base-200">
            <li class="p-4 text-sm text-base-content/50 text-center">
                <?= __('notifications.no_notifications', 'No notifications yet') ?>
            </li>
        </ul>
        <div class="p-2 border-t border-base-300 text-center">
            <button id="notif-mark-all" class="btn btn-xs btn-ghost w-full hidden">
                <?= __('notifications.mark_all_read', 'Mark all as read') ?>
            </button>
        </div>
    </div>
</details>
