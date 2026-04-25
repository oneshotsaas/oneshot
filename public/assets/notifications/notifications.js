(function () {
    'use strict';

    const API_URL   = document.documentElement.dataset.siteUrl
        ? document.documentElement.dataset.siteUrl.replace(/\/$/, '') + '/api/v1/notifications/unread'
        : '/api/v1/notifications/unread';
    const PREF_URL  = '/app/notifications/preference';
    const READ_URL  = '/app/notifications/read/';
    const RALL_URL  = '/app/notifications/read-all';
    const POLL_MS   = 30000;

    const badge    = document.getElementById('notif-badge');
    const list     = document.getElementById('notif-list');
    const markAll  = document.getElementById('notif-mark-all');

    const dropdown = document.getElementById('notif-dropdown');
    if (dropdown) {
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                dropdown.removeAttribute('open');
            }
        });
    }

    function ajaxHeaders() {
        return { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' };
    }

    function poll() {
        if (document.hidden) return;
        fetch(API_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data || !data.success) return;
                renderBadge(data.data.count);
                renderList(data.data.items);
            })
            .catch(() => {});
    }

    function renderBadge(count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function renderList(items) {
        if (!list) return;
        if (!items || items.length === 0) {
            list.innerHTML = '<li class="p-4 text-sm text-base-content/50 text-center">No notifications yet</li>';
            if (markAll) markAll.classList.add('hidden');
            return;
        }
        list.innerHTML = items.map(n => `
            <li class="flex items-start gap-2 p-3 hover:bg-base-200 transition-colors" data-id="${n.id}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium leading-tight">${escHtml(n.title)}</p>
                    <p class="text-xs text-base-content/40 mt-0.5">${escHtml(n.created_at || '')}</p>
                </div>
                ${n.url ? `<a href="${escHtml(n.url)}" class="btn btn-xs btn-ghost shrink-0">→</a>` : ''}
                ${!n.read_at ? `<button class="btn btn-xs btn-ghost shrink-0 notif-read-btn" data-id="${n.id}">✓</button>` : ''}
            </li>
        `).join('');
        if (markAll) markAll.classList.remove('hidden');

        list.querySelectorAll('.notif-read-btn').forEach(btn => {
            btn.addEventListener('click', () => markRead(btn.dataset.id));
        });
    }

    function markRead(id) {
        fetch(READ_URL + id, { method: 'POST', headers: ajaxHeaders() })
            .then(() => poll());
    }

    if (markAll) {
        markAll.addEventListener('click', () => {
            fetch(RALL_URL, { method: 'POST', headers: ajaxHeaders() })
                .then(() => poll());
        });
    }

    function escHtml(str) {
        return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // Preference toggles (on profile page)
    let prefDebounce = {};
    document.querySelectorAll('[data-type][data-channel]').forEach(toggle => {
        toggle.addEventListener('change', () => {
            const key = toggle.dataset.type + '.' + toggle.dataset.channel;
            clearTimeout(prefDebounce[key]);
            prefDebounce[key] = setTimeout(() => {
                fetch(PREF_URL, {
                    method: 'POST',
                    headers: ajaxHeaders(),
                    body: JSON.stringify({ type: toggle.dataset.type, channel: toggle.dataset.channel, enabled: toggle.checked })
                });
            }, 300);
        });
    });

    // ── Notifications page: per-item mark-read buttons ────────────────────────
    document.querySelectorAll('.notif-page-read').forEach(btn => {
        btn.addEventListener('click', () => {
            fetch(btn.dataset.url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => { if (r.ok) btn.closest('[data-id]').classList.add('opacity-60'); });
        });
    });

    // ── Notifications page: mark-all button ───────────────────────────────────
    const pageMeta   = document.getElementById('notif-page-meta');
    const pageMarkAll = document.getElementById('notif-page-mark-all');
    if (pageMeta && pageMarkAll) {
        pageMarkAll.addEventListener('click', () => {
            fetch(pageMeta.dataset.markAllUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => { if (r.ok) document.querySelectorAll('#notif-page-list [data-id]').forEach(el => el.classList.add('opacity-60')); });
        });
    }

    // Initial poll + interval
    poll();
    setInterval(poll, POLL_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();
