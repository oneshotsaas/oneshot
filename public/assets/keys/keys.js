/**
 * keys.js — API Key management page interactions
 */
(function () {
    'use strict';

    // ── Limit rows ───────────────────────────────────────────────────────────

    function makeLimitRow(type, days, max) {
        const tpl = document.getElementById('limit-row-tpl');
        const row = tpl.content.cloneNode(true).firstElementChild;

        const daysInput = row.querySelector('[data-field="days"]');
        const maxInput  = row.querySelector('[data-field="max"]');

        daysInput.name = 'limits_' + type + '[days][]';
        maxInput.name  = 'limits_' + type + '[max][]';

        if (days !== undefined) daysInput.value = days;
        if (max  !== undefined && max !== '') maxInput.value = max;

        row.querySelector('[data-remove-limit]').addEventListener('click', function () {
            row.remove();
        });

        return row;
    }

    function initLimitRows() {
        ['requests', 'credits'].forEach(function (type) {
            const container = document.getElementById('limits-' + type + '-rows');
            if (!container) return;

            const existing = container.getAttribute('data-existing');
            if (existing) {
                try {
                    JSON.parse(existing).forEach(function (r) {
                        container.appendChild(makeLimitRow(type, r.days, r.max));
                    });
                } catch (e) {}
            }

            const addBtn = document.querySelector('[data-add-limit="' + type + '"]');
            if (addBtn) {
                addBtn.addEventListener('click', function () {
                    container.appendChild(makeLimitRow(type, 0, ''));
                });
            }
        });
    }

    // ── Expire type toggle ───────────────────────────────────────────────────

    function initExpireToggle() {
        const radios = document.querySelectorAll('[data-expire-toggle]');
        if (!radios.length) return;

        function update() {
            radios.forEach(function (radio) {
                if (!radio.checked) return;
                const mode = radio.getAttribute('data-expire-toggle');
                const daysWrap = document.getElementById('expire-days-wrap');
                const dateWrap = document.getElementById('expire-date-wrap');
                if (daysWrap) daysWrap.classList.toggle('hidden', mode !== 'days');
                if (dateWrap) dateWrap.classList.toggle('hidden', mode !== 'date');
            });
        }

        radios.forEach(function (r) { r.addEventListener('change', update); });
        update();
    }

    // ── Submit loading state ─────────────────────────────────────────────────

    function initSubmitLoading() {
        const form = document.getElementById('keys-form');
        const btn  = document.getElementById('keys-submit');
        if (!form || !btn) return;

        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.classList.add('loading', 'loading-spinner', 'loading-xs');
        });
    }

    // ── Init ─────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        initLimitRows();
        initExpireToggle();
        initSubmitLoading();
    });
}());
