<?php

namespace OneShot\Settings\Models;

use OneShot\Core\Models\Base;

class Setting extends Base
{
    protected $table         = 'settings';
    protected $allowedFields = ['key', 'value', 'type', 'options', 'user_id', 'label', 'sort'];
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    private static array $cache  = [];
    private static array $rows   = []; // full row objects by scope ('') then row->key
    private static array $loaded = []; // loaded scopes

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    public function fetch(string $key, mixed $default = null, ?int $userId = null): mixed
    {
        $scope = (string) $userId;

        if (empty(self::$loaded[$scope])) {
            $this->loadAll($userId);
        }

        $cacheKey = $key . ':' . $scope;

        // user scope: fall back to global if not overridden
        if ($userId !== null && ! array_key_exists($cacheKey, self::$cache)) {
            $cacheKey = $key . ':';
        }

        return array_key_exists($cacheKey, self::$cache)
            ? (self::$cache[$cacheKey] ?? $default)
            : $default;
    }

    public function store(string $key, mixed $value, ?int $userId = null): void
    {
        $encrypted = encrypt((string) $value);

        $existing = $this->where('key', $key)
            ->where('user_id', $userId)
            ->limit(1)->first();

        if ($existing) {
            $this->update($existing->id, ['value' => $encrypted]);
        } else {
            $this->insert(['key' => $key, 'value' => $encrypted, 'user_id' => $userId]);
        }

        $cacheKey = $key . ':' . (string) $userId;
        self::$cache[$cacheKey] = (string) $value;
    }

    /** One query: loads global rows + user rows. Populates both scopes. */
    public function loadAll(?int $userId = null): void
    {
        // Resolve user from session if not given — allows a single combined query
        if ($userId === null) {
            $userId = (int) (service('session')->get('user_id') ?? 0) ?: null;
        }

        if ($userId !== null) {
            $rows = $this->groupStart()
                ->where('user_id', null)
                ->orWhere('user_id', $userId)
                ->groupEnd()
                ->findAll();
        } else {
            $rows = $this->where('user_id', null)->findAll();
        }

        self::$rows[''] = [];
        if ($userId !== null) {
            self::$rows[(string) $userId] = [];
        }

        foreach ($rows as $row) {
            $scope = (string) $row->user_id;
            self::$cache[$row->key . ':' . $scope] = decrypt($row->value ?? '');
            self::$rows[$scope][$row->key] = $row;
        }

        self::$loaded[''] = true;
        if ($userId !== null) {
            self::$loaded[(string) $userId] = true;
        }
    }

    /** Alias kept for explicit preload after login */
    public function preload(int $userId): void
    {
        $this->loadAll($userId);
    }

    /** Return rows for a group (key prefix before dot) for form building */
    public function fields(string $group): array
    {
        if (empty(self::$loaded[''])) {
            $this->loadAll(null);
        }

        $prefix = $group . '.';
        $result = [];

        foreach (self::$rows[''] as $key => $row) {
            if (str_starts_with($key, $prefix)) {
                $result[] = $row;
            }
        }

        usort($result, fn($a, $b) => ($a->sort ?? 0) <=> ($b->sort ?? 0));

        return $result;
    }

    /** Distinct group names from global settings */
    public function groups(): array
    {
        if (empty(self::$loaded[''])) {
            $this->loadAll(null);
        }

        $found = [];
        foreach (array_keys(self::$rows['']) as $key) {
            $g = strstr($key, '.', true) ?: 'general';
            $found[$g] = $g;
        }

        $order  = ['general', 'appearance', 'branding'];
        $sorted = [];
        foreach ($order as $g) {
            if (isset($found[$g])) {
                $sorted[] = $g;
                unset($found[$g]);
            }
        }
        foreach ($found as $g) {
            $sorted[] = $g;
        }

        return $sorted;
    }

    // ------------------------------------------------------------------
    // CSS file helper
    // ------------------------------------------------------------------

    public static function writeCssFile(string $section, string $mode, string $css): void
    {
        $section = match (true) {
            in_array($section, ['admin', 'app', 'front'], true) => $section,
            default => throw new \InvalidArgumentException('Invalid section'),
        };

        $mode = match (true) {
            in_array($mode, ['light', 'dark'], true) => $mode,
            default => throw new \InvalidArgumentException('Invalid mode'),
        };

        $dir  = FCPATH . 'assets/css/themes/';
        $file = $dir . 'custom-' . $section . '-' . $mode . '.css';

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $css = self::normalizeThemeCss($css);

        if (file_exists($file) && file_get_contents($file) === $css) {
            return;
        }

        file_put_contents($file, $css);
    }

    /**
     * Convert DaisyUI theme generator format (@plugin "daisyui/theme" { ... })
     * to standard CSS ([data-theme="name"] { ... }).
     * Plain CSS is returned as-is.
     */
    private static function normalizeThemeCss(string $css): string
    {
        $css = trim($css);

        if (! str_starts_with($css, '@plugin')) {
            return $css;
        }

        $start = strpos($css, '{');
        $end   = strrpos($css, '}');

        if ($start === false || $end === false) {
            return $css;
        }

        $body = substr($css, $start + 1, $end - $start - 1);
        $name = 'custom';
        $lines = [];

        foreach (explode("\n", $body) as $line) {
            $line = trim(rtrim(trim($line), ';'));

            if ($line === '') {
                continue;
            }

            $colonPos = strpos($line, ':');

            if ($colonPos === false) {
                continue;
            }

            $key   = trim(substr($line, 0, $colonPos));
            $value = trim(trim(substr($line, $colonPos + 1)), '"\'');

            if ($key === 'name') {
                $name = $value;
                continue;
            }

            if (in_array($key, ['default', 'prefersdark'], true)) {
                continue;
            }

            $lines[] = '  ' . $key . ': ' . $value . ';';
        }

        return '[data-theme] {' . "\n"
            . implode("\n", $lines) . "\n"
            . '}' . "\n";
    }

}
