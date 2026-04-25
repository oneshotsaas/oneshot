<?php

namespace OneShot\Auth\Database\Seeds;

/**
 * Auth + Mail settings seeder.
 * Idempotent: skips existing rows, always refreshes readonly (computed) values.
 */
class AuthSeeder
{
    public function run(): void
    {
        helper('oneshot');

        $rows = [
            // Mail / SMTP
            ['key' => 'mail.smtp_host',   'type' => 'text',     'label' => 'SMTP Host',     'sort' => 1, 'options' => null],
            ['key' => 'mail.smtp_port',   'type' => 'text',     'label' => 'SMTP Port',     'sort' => 2, 'options' => null],
            ['key' => 'mail.smtp_user',   'type' => 'text',     'label' => 'SMTP Username', 'sort' => 3, 'options' => null],
            ['key' => 'mail.smtp_pass',   'type' => 'password', 'label' => 'SMTP Password', 'sort' => 4, 'options' => null],
            ['key' => 'mail.smtp_crypto', 'type' => 'select',   'label' => 'Encryption',    'sort' => 5,
                'options' => json_encode([
                    ['value' => 'tls',  'label' => 'TLS (port 587)'],
                    ['value' => 'ssl',  'label' => 'SSL (port 465)'],
                    ['value' => 'none', 'label' => 'None'],
                ])],
            ['key' => 'mail.from_email', 'type' => 'text', 'label' => 'From Email', 'sort' => 6, 'options' => null],
            ['key' => 'mail.from_name',  'type' => 'text', 'label' => 'From Name',  'sort' => 7, 'options' => null],

            // Auth — behavior
            ['key' => 'auth.email_verification', 'type' => 'select', 'label' => 'Email Verification', 'sort' => 1,
                'options' => json_encode([
                    ['value' => 'required', 'label' => 'Required (Recommended)'],
                    ['value' => 'optional', 'label' => 'Optional'],
                    ['value' => 'disabled', 'label' => 'Disabled'],
                ])],
            ['key' => 'auth.normalize_email',         'type' => 'boolean',  'label' => '', 'sort' => 2, 'options' => null],
            ['key' => 'auth.block_disposable_emails', 'type' => 'boolean',  'label' => '', 'sort' => 3, 'options' => null],
            ['key' => 'auth.blocked_email_domains',   'type' => 'textarea', 'label' => '', 'sort' => 4, 'options' => null],
            ['key' => 'auth.deleted_email_policy', 'type' => 'select', 'label' => '', 'sort' => 5,
                'options' => json_encode([
                    ['value' => 'allow', 'label' => 'Allow (register as new user)'],
                    ['value' => 'block', 'label' => 'Block (return email taken error)'],
                    ['value' => 'flag',  'label' => 'Flag (allow but log warning)'],
                ])],
            ['key' => 'auth.password_min_length',        'type' => 'text',    'label' => '', 'sort' => 6, 'options' => null],
            ['key' => 'auth.password_require_uppercase', 'type' => 'boolean', 'label' => '', 'sort' => 7, 'options' => null],
            ['key' => 'auth.password_require_numbers',   'type' => 'boolean', 'label' => '', 'sort' => 8, 'options' => null],
            ['key' => 'auth.password_require_symbols',   'type' => 'boolean', 'label' => '', 'sort' => 9, 'options' => null],

            // Google
            ['key' => 'auth.oauth_google_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 10, 'options' => null],
            ['key' => 'auth.oauth_google_callback', 'type' => 'readonly', 'label' => '', 'sort' => 11, 'options' => null],
            ['key' => 'auth.oauth_google_id',       'type' => 'text',     'label' => '', 'sort' => 12, 'options' => null],
            ['key' => 'auth.oauth_google_secret',   'type' => 'password', 'label' => '', 'sort' => 13, 'options' => null],
            // Facebook
            ['key' => 'auth.oauth_facebook_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 20, 'options' => null],
            ['key' => 'auth.oauth_facebook_callback', 'type' => 'readonly', 'label' => '', 'sort' => 21, 'options' => null],
            ['key' => 'auth.oauth_facebook_id',       'type' => 'text',     'label' => '', 'sort' => 22, 'options' => null],
            ['key' => 'auth.oauth_facebook_secret',   'type' => 'password', 'label' => '', 'sort' => 23, 'options' => null],
            // GitHub
            ['key' => 'auth.oauth_github_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 30, 'options' => null],
            ['key' => 'auth.oauth_github_callback', 'type' => 'readonly', 'label' => '', 'sort' => 31, 'options' => null],
            ['key' => 'auth.oauth_github_id',       'type' => 'text',     'label' => '', 'sort' => 32, 'options' => null],
            ['key' => 'auth.oauth_github_secret',   'type' => 'password', 'label' => '', 'sort' => 33, 'options' => null],
            // Apple
            ['key' => 'auth.oauth_apple_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 40, 'options' => null],
            ['key' => 'auth.oauth_apple_callback', 'type' => 'readonly', 'label' => '', 'sort' => 41, 'options' => null],
            ['key' => 'auth.oauth_apple_id',       'type' => 'text',     'label' => '', 'sort' => 42, 'options' => null],
            ['key' => 'auth.oauth_apple_team_id',  'type' => 'text',     'label' => '', 'sort' => 43, 'options' => null],
            ['key' => 'auth.oauth_apple_key_id',   'type' => 'text',     'label' => '', 'sort' => 44, 'options' => null],
            ['key' => 'auth.oauth_apple_secret',   'type' => 'password', 'label' => '', 'sort' => 45, 'options' => null],
            // LinkedIn
            ['key' => 'auth.oauth_linkedin_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 50, 'options' => null],
            ['key' => 'auth.oauth_linkedin_callback', 'type' => 'readonly', 'label' => '', 'sort' => 51, 'options' => null],
            ['key' => 'auth.oauth_linkedin_id',       'type' => 'text',     'label' => '', 'sort' => 52, 'options' => null],
            ['key' => 'auth.oauth_linkedin_secret',   'type' => 'password', 'label' => '', 'sort' => 53, 'options' => null],
            // Microsoft
            ['key' => 'auth.oauth_microsoft_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 60, 'options' => null],
            ['key' => 'auth.oauth_microsoft_callback', 'type' => 'readonly', 'label' => '', 'sort' => 61, 'options' => null],
            ['key' => 'auth.oauth_microsoft_id',       'type' => 'text',     'label' => '', 'sort' => 62, 'options' => null],
            ['key' => 'auth.oauth_microsoft_secret',   'type' => 'password', 'label' => '', 'sort' => 63, 'options' => null],
            // Telegram OAuth
            ['key' => 'auth.oauth_telegram_enabled',   'type' => 'boolean',  'label' => '', 'sort' => 70, 'options' => null],
            ['key' => 'auth.oauth_telegram_callback',  'type' => 'readonly', 'label' => '', 'sort' => 71, 'options' => null],
            ['key' => 'auth.oauth_telegram_bot_token', 'type' => 'password', 'label' => '', 'sort' => 72, 'options' => null],
            ['key' => 'auth.oauth_telegram_bot_name',  'type' => 'text',     'label' => '', 'sort' => 73, 'options' => null],
        ];

        $defaults = [
            'mail.smtp_port'            => '587',
            'mail.smtp_crypto'          => 'tls',
            'auth.deleted_email_policy' => 'allow',
            'auth.email_verification'   => 'required',
            'auth.password_min_length'  => '8',
        ];

        $db = \Config\Database::connect();

        foreach ($rows as $row) {
            $existing = $db->table('settings')
                ->where('key', $row['key'])
                ->where('user_id IS NULL', null, false)
                ->get()->getRowObject();

            if ($existing) {
                $update = [
                    'sort'    => $row['sort'],
                    'type'    => $row['type'],
                    'options' => $row['options'],
                ];
                // readonly fields store computed values — always refresh (domain/secret may change)
                if ($row['type'] === 'readonly') {
                    $update['value'] = encrypt($this->resolveReadonly($row['key']));
                }
                $db->table('settings')->where('id', $existing->id)->update($update);
                continue;
            }

            $value = $row['type'] === 'readonly'
                ? $this->resolveReadonly($row['key'])
                : ($defaults[$row['key']] ?? '');

            $db->table('settings')->insert([
                'key'     => $row['key'],
                'value'   => encrypt($value),
                'type'    => $row['type'],
                'options' => $row['options'],
                'label'   => $row['label'],
                'sort'    => $row['sort'],
                'user_id' => null,
            ]);
        }
    }

    private function resolveReadonly(string $key): string
    {
        if (preg_match('/^auth\.oauth_(\w+)_callback$/', $key, $m)) {
            return \OneShot\Auth\Services\OAuthService::callbackUrl($m[1]);
        }
        return '';
    }
}
