// Billing module JS

// Refund preview calculator
(function () {
    var root = document.getElementById('refund-root');
    if (!root) return;

    var invoiceAmount  = parseFloat(root.dataset.invoiceAmount  || 0);
    var creditsGranted = parseFloat(root.dataset.creditsGranted || 0);
    var currentBalance = parseFloat(root.dataset.currentBalance || 0);

    function update() {
        var refundCents = Math.round(parseFloat(document.getElementById('refund_amount').value || 0) * 100);
        var action      = document.getElementById('credits_action').value;
        var toDeduct    = 0;

        if (action === 'all') {
            toDeduct = creditsGranted;
        } else if (action === 'none') {
            toDeduct = 0;
        } else {
            toDeduct = invoiceAmount > 0 && creditsGranted > 0
                ? Math.round(creditsGranted * (refundCents / invoiceAmount) * 10000) / 10000
                : 0;
        }

        var balanceAfter = currentBalance - toDeduct;

        document.getElementById('preview-credits').textContent = toDeduct.toFixed(4);
        var balanceEl = document.getElementById('preview-balance');
        balanceEl.textContent = balanceAfter.toFixed(4);
        balanceEl.className = 'tabular-nums font-mono' + (balanceAfter < 0 ? ' text-error' : '');
    }

    document.getElementById('refund_amount').addEventListener('input', update);
    document.getElementById('credits_action').addEventListener('change', update);
    update();
})();

// Provider sync buttons
(function () {
    function getCsrf() {
        var input = document.querySelector('input[name^="csrf_"]');
        if (!input) return {};
        return { name: input.name, value: input.value };
    }

    document.querySelectorAll('.js-sync-provider').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var block    = btn.closest('[data-provider]');
            var url      = block.dataset.syncUrl;
            var result   = block.querySelector('.js-sync-result');
            var csrf     = getCsrf();
            var body     = csrf.name ? csrf.name + '=' + encodeURIComponent(csrf.value) : '';
            var origText = btn.textContent;

            btn.disabled = true;
            btn.textContent = '...';
            result.classList.remove('hidden');
            result.textContent = 'Syncing...';

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body,
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    result.textContent = 'Done — synced: ' + data.synced + ', archived: ' + data.archived + ', skipped: ' + data.skipped + (data.errors && data.errors.length ? ' | errors: ' + data.errors.join('; ') : '');
                } else {
                    result.textContent = 'Error: ' + (data.message || 'unknown');
                }
            })
            .catch(function (e) {
                result.textContent = 'Request failed: ' + e.message;
            })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = origText;
            });
        });
    });
})();
