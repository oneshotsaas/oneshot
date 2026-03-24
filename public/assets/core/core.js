/**
 * Core JS — shared UI behaviours loaded in every layout.
 *
 * Convention: use data attributes to activate behaviour, no inline JS in views.
 *
 * Included behaviours:
 *   - [data-toggle-password] — show/hide password field
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Flash Dismiss ─────────────────────────────────────────────────────────
    document.querySelectorAll('.js-flash-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var el = btn.closest('.alert');
            if (!el) return;
            el.style.transition = 'opacity 0.3s, transform 0.3s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-4px)';
            setTimeout(function () { el.remove(); }, 300);
        });
    });

    // ── Password Toggle ───────────────────────────────────────────────────────
    // Usage: .js-toggle-password button inside a .relative wrapper with an input.
    document.querySelectorAll('.js-toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.relative').querySelector('input');
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('.icon-eye').classList.toggle('hidden', show);
            btn.querySelector('.icon-eye-off').classList.toggle('hidden', !show);
        });
    });

    // ── Copy to Clipboard ─────────────────────────────────────────────────────
    // Usage: .js-copy-value button inside a .relative wrapper with an input.
    document.querySelectorAll('.js-copy-value').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.relative').querySelector('input');
            if (!input) return;

            function flash() {
                var copy  = btn.querySelector('.icon-copy');
                var check = btn.querySelector('.icon-check');
                copy.classList.add('hidden');
                check.classList.remove('hidden');
                setTimeout(function () {
                    copy.classList.remove('hidden');
                    check.classList.add('hidden');
                }, 1500);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value).then(flash);
            } else {
                // Fallback for HTTP / older browsers
                var tmp = document.createElement('input');
                tmp.value = input.value;
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                document.body.removeChild(tmp);
                flash();
            }
        });
    });

});
