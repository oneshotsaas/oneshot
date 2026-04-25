/**
 * Settings module JS
 * Handles: theme pickers, section tabs, mode tabs, notification defaults toggles
 */

// Enable live theme preview on this page
document.documentElement.dataset.liveTheme = '1';
document.body.dataset.liveTheme = '1';

// ── Theme Pickers ─────────────────────────────────────────────────────────────
document.querySelectorAll('.theme-picker[data-picker-id]').forEach(function (picker) {
    const pickerId   = picker.dataset.pickerId;
    const savedTheme = picker.dataset.savedTheme;

    function selectTheme(name) {
        const input = document.getElementById('input-' + pickerId);
        const label = document.getElementById('label-' + pickerId);
        if (!input) return;

        input.value = name;
        if (label) label.textContent = name.charAt(0).toUpperCase() + name.slice(1);

        picker.querySelectorAll('[data-picker="' + pickerId + '"]').forEach(function (card) {
            const active = card.dataset.themeName === name;
            card.classList.toggle('border-primary',       active);
            card.classList.toggle('ring-2',               active);
            card.classList.toggle('ring-primary',         active);
            card.classList.toggle('ring-offset-2',        active);
            card.classList.toggle('ring-offset-base-100', active);
            card.classList.toggle('border-base-300',     !active);
            const chk = card.querySelector('.theme-check');
            if (chk) {
                chk.classList.toggle('opacity-0',   !active);
                chk.classList.toggle('opacity-100',  active);
            }
        });

        if (document.body.dataset.liveTheme === '1') {
            document.documentElement.dataset.theme = name;
        }
    }

    picker.addEventListener('click', function (e) {
        const card = e.target.closest('[data-picker="' + pickerId + '"]');
        if (card) { selectTheme(card.dataset.themeName); return; }

        const resetBtn = e.target.closest('[data-reset-picker="' + pickerId + '"]');
        if (resetBtn) selectTheme(savedTheme);
    });
});

// ── Section Tabs ──────────────────────────────────────────────────────────────
document.querySelectorAll('.section-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const sec = btn.dataset.sec;

        document.querySelectorAll('.section-panel').forEach(function (p) {
            p.hidden = p.id !== 'panel-' + sec;
        });

        document.querySelectorAll('.section-tab').forEach(function (b) {
            const active = b.dataset.sec === sec;
            b.classList.toggle('bg-base-100',          active);
            b.classList.toggle('shadow',               active);
            b.classList.toggle('text-base-content',    active);
            b.classList.toggle('text-base-content/50', !active);
        });
    });
});

// ── Admin Notification Defaults Toggles ──────────────────────────────────────
(function () {
    var meta = document.getElementById('js-notif-defaults');
    if (!meta) return;

    var url      = meta.dataset.url;
    var csrfName = meta.dataset.csrfName;
    var csrfHash = meta.dataset.csrfHash;

    document.querySelectorAll('.admin-notif-toggle').forEach(function (el) {
        el.addEventListener('change', function () {
            var toggle = this;
            var body   = new URLSearchParams();
            body.set(csrfName, csrfHash);
            body.set('setting', toggle.dataset.setting);
            body.set('field',   toggle.dataset.field);
            body.set('enabled', toggle.checked ? '1' : '0');

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body,
            }).then(function (r) {
                if (r.ok) {
                    r.json().then(function (d) { if (d.csrf_hash) csrfHash = d.csrf_hash; });
                } else {
                    toggle.checked = !toggle.checked;
                }
            }).catch(function () { toggle.checked = !toggle.checked; });
        });
    });
}());

// ── Mode Tabs (Light / Dark) ──────────────────────────────────────────────────
document.querySelectorAll('.mode-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const sec  = btn.dataset.sec;
        const mode = btn.dataset.mode;

        document.querySelectorAll('#panel-' + sec + ' .mode-panel').forEach(function (p) {
            p.hidden = p.id !== 'panel-' + sec + '-' + mode;
        });

        document.querySelectorAll('#mode-tabs-' + sec + ' .mode-tab').forEach(function (b) {
            const active = b.dataset.mode === mode;
            b.classList.toggle('bg-base-100',          active);
            b.classList.toggle('shadow',               active);
            b.classList.toggle('text-base-content',    active);
            b.classList.toggle('text-base-content/50', !active);
        });

        // Live preview: switch to the theme of the newly visible mode panel
        if (document.body.dataset.liveTheme === '1') {
            const input = document.querySelector('#panel-' + sec + '-' + mode + ' input[type=hidden]');
            if (input) document.documentElement.dataset.theme = input.value;
        }
    });
});
