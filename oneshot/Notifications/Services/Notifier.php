<?php

namespace OneShot\Notifications\Services;

use OneShot\Core\Services\Base;
use OneShot\Notifications\Channels\InApp;
use OneShot\Notifications\Channels\Email;
use OneShot\Notifications\Channels\Telegram;
use OneShot\Notifications\Models\Notification;
use OneShot\Settings\Models\Setting;

class Notifier extends Base
{
    private static array   $prefsCache    = [];
    private static ?array  $adminCache    = null;
    private static ?array  $channelsCache = null;

    // ── Notify ──────────────────────────────────────────────────────────────

    public function notify(int $userId, string $type, string $title, string $url = '', array $data = []): void
    {
        $cfg = config('NotificationTypes')->types[$type] ?? null;
        if (!$cfg) return;

        $user = (new \OneShot\Auth\Models\User())->getById($userId);
        if (!$user) return;

        foreach ($cfg['channels'] as $channel) {
            if (!$this->isChannelAvailable($channel)) continue;
            if (!$this->isEnabled($userId, $type, $channel, $cfg['defaults'])) continue;
            match ($channel) {
                'in_app'   => (new InApp())->send($userId, $type, $title, $url, $data),
                'email'    => (new Email())->send($user, $title, $data),
                'telegram' => (new Telegram())->send($user, $title, $data),
                default    => null,
            };
        }
    }

    // ── Preferences ─────────────────────────────────────────────────────────

    private function getPrefsArray(int $userId): array
    {
        if (!isset(self::$prefsCache[$userId])) {
            $raw = (new Setting())->fetch('notifications.prefs', '{}', $userId);
            self::$prefsCache[$userId] = json_decode($raw ?: '{}', true) ?: [];
        }
        return self::$prefsCache[$userId];
    }

    private function isChannelAvailable(string $channel): bool
    {
        if ($channel === 'in_app') return true;

        if (self::$channelsCache === null) {
            $raw = (new Setting())->fetch('notifications.channels', '["email","telegram"]');
            self::$channelsCache = json_decode($raw ?: '[]', true) ?: [];
        }

        return in_array($channel, self::$channelsCache, true);
    }

    private function getAdminDefaults(): array
    {
        if (self::$adminCache === null) {
            $raw = (new Setting())->fetch('notifications.defaults', '{}');
            self::$adminCache = json_decode($raw ?: '{}', true) ?: [];
        }
        return self::$adminCache;
    }

    private function isEnabled(int $userId, string $type, string $channel, array $codeDefaults): bool
    {
        $key   = $type . '.' . $channel;
        $prefs = $this->getPrefsArray($userId);
        if (array_key_exists($key, $prefs)) return (bool)$prefs[$key];
        $admin = $this->getAdminDefaults();
        if (array_key_exists($key, $admin)) return (bool)$admin[$key];
        return $codeDefaults[$channel] ?? false;
    }

    public function updatePreference(int $userId, string $type, string $channel, bool $enabled): void
    {
        $setting = new Setting();
        $prefs   = $this->getPrefsArray($userId);
        $prefs[$type . '.' . $channel] = $enabled;
        $setting->store('notifications.prefs', json_encode($prefs), $userId);
        self::$prefsCache[$userId] = $prefs;
    }

    public function getPreferences(int $userId, ?string $audience = null): array
    {
        $types  = config('NotificationTypes');
        $result = [];
        foreach ($types->types as $typeKey => $cfg) {
            if ($audience !== null) {
                $typeAudience = $cfg['audience'] ?? 'user';
                if ($typeAudience !== $audience && $typeAudience !== 'all') {
                    continue;
                }
            }
            foreach ($cfg['channels'] as $ch) {
                $result[$typeKey . '.' . $ch] = $this->isEnabled($userId, $typeKey, $ch, $cfg['defaults']);
            }
        }
        return $result;
    }

    public function getPreferenceTypes(int $userId, ?string $audience = null): array
    {
        $types  = config('NotificationTypes');
        $result = [];
        foreach ($types->types as $typeKey => $cfg) {
            if ($audience !== null) {
                $typeAudience = $cfg['audience'] ?? 'user';
                if ($typeAudience !== $audience && $typeAudience !== 'all') {
                    continue;
                }
            }
            $prefs = [];
            foreach ($cfg['channels'] as $ch) {
                $prefs[$ch] = $this->isEnabled($userId, $typeKey, $ch, $cfg['defaults']);
            }
            $result[$typeKey] = array_merge($cfg, ['prefs' => $prefs]);
        }
        return $result;
    }

    // ── Read / Unread ────────────────────────────────────────────────────────

    public function getUnread(int $userId, int $limit = 10): array
    {
        return (new Notification())->getUnread($userId, $limit);
    }

    public function countUnread(int $userId): int
    {
        return (new Notification())->countUnread($userId);
    }

    public function markRead(int $userId, int $id): void
    {
        (new Notification())->where('id', $id)->where('user_id', $userId)
            ->set('read_at', date('Y-m-d H:i:s'))->update();
    }

    public function markAllRead(int $userId): void
    {
        (new Notification())->where('user_id', $userId)->where('read_at IS NULL')
            ->set('read_at', date('Y-m-d H:i:s'))->update();
    }
}
