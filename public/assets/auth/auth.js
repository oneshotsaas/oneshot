/**
 * Auth module JS
 * - Show/hide password toggle
 * - Copy password to clipboard
 * - Generate password from policy
 * - Live rule validation (rules go green on satisfy)
 */
(function () {
    'use strict';

    // ------------------------------------------------------------------
    // Password policy — injected via data-policy on the form
    // ------------------------------------------------------------------
    function getPolicy(form) {
        try { return JSON.parse(form.dataset.policy || '{}'); } catch { return {}; }
    }

    // ------------------------------------------------------------------
    // Show / hide toggle
    // ------------------------------------------------------------------
    function initToggle(btn) {
        const input = btn.closest('.pw-wrap').querySelector('input[type=password], input[type=text]');
        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('.icon-eye')    .classList.toggle('hidden', show);
            btn.querySelector('.icon-eye-off').classList.toggle('hidden', !show);
            (btn.closest('.tooltip') || btn).dataset.tip = show ? btn.dataset.labelHide : btn.dataset.labelShow;
        });
    }

    // ------------------------------------------------------------------
    // Generator — builds a password that satisfies all active rules
    // ------------------------------------------------------------------
    const CHARS = {
        lower:   'abcdefghijklmnopqrstuvwxyz',
        upper:   'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        numbers: '0123456789',
        symbols: '!@#$%^&*-_=+?',
    };

    function randomChar(pool) {
        return pool[crypto.getRandomValues(new Uint32Array(1))[0] % pool.length];
    }

    function generatePassword() {
        const len = 14 + crypto.getRandomValues(new Uint32Array(1))[0] % 10; // 14–23
        let required = [
            randomChar(CHARS.lower),
            randomChar(CHARS.upper),
            randomChar(CHARS.numbers),
            randomChar(CHARS.symbols),
        ];

        const pool = CHARS.lower + CHARS.upper + CHARS.numbers + CHARS.symbols;

        while (required.length < len) required.push(randomChar(pool));

        // Fisher-Yates shuffle
        for (let i = required.length - 1; i > 0; i--) {
            const j = crypto.getRandomValues(new Uint32Array(1))[0] % (i + 1);
            [required[i], required[j]] = [required[j], required[i]];
        }

        // Ensure first and last chars are never symbols
        const isSymbol = c => !(/[A-Za-z0-9]/.test(c));
        const safeIdx  = () => required.findIndex((c, idx) => idx !== 0 && idx !== required.length - 1 && !isSymbol(c));
        if (isSymbol(required[0])) {
            const j = safeIdx();
            if (j !== -1) [required[0], required[j]] = [required[j], required[0]];
        }
        if (isSymbol(required[required.length - 1])) {
            const j = safeIdx();
            if (j !== -1) [required[required.length - 1], required[j]] = [required[j], required[required.length - 1]];
        }

        return required.join('');
    }

    // ------------------------------------------------------------------
    // Live rule indicators
    // ------------------------------------------------------------------
    const CHECKS = {
        min_length: (v, p) => v.length >= (p.min_length || 8),
        uppercase:  (v)    => /[A-Z]/.test(v) && /[a-z]/.test(v),
        numbers:    (v)    => /[0-9]/.test(v),
        symbols:    (v)    => /[^A-Za-z0-9]/.test(v),
    };

    function updateRules(input, ruleEls, policy) {
        const val = input.value;
        ruleEls.forEach(el => {
            const rule = el.dataset.rule;
            if (!CHECKS[rule]) return;
            const ok = CHECKS[rule](val, policy);
            el.classList.toggle('text-success', ok);
            el.classList.toggle('opacity-40',  !ok);
            el.classList.remove(ok ? 'opacity-40' : 'text-success');
        });
    }

    // ------------------------------------------------------------------
    // Init per-form
    // ------------------------------------------------------------------
    function initForm(form) {
        const policy = getPolicy(form);

        // Show/hide toggles
        form.querySelectorAll('.js-pw-toggle').forEach(initToggle);

        // Generate button
        form.querySelectorAll('.js-pw-generate').forEach(btn => {
            btn.addEventListener('click', () => {
                const wrap   = btn.closest('.pw-wrap');
                const input  = wrap.querySelector('input');
                const toggle = wrap.querySelector('.js-pw-toggle');
                input.value = generatePassword();
                input.type  = 'text';
                if (toggle) {
                    toggle.querySelector('.icon-eye')    ?.classList.add('hidden');
                    toggle.querySelector('.icon-eye-off')?.classList.remove('hidden');
                    (toggle.closest('.tooltip') || toggle).dataset.tip = toggle.dataset.labelHide;
                }
                input.dispatchEvent(new Event('input'));
            });
        });

        // Live rule validation
        const ruleEls = form.querySelectorAll('[data-rule]');
        if (!ruleEls.length) return;

        form.querySelectorAll('input[name=password]').forEach(input => {
            input.addEventListener('input', () => updateRules(input, ruleEls, policy));
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-policy]').forEach(initForm);
    });
})();
