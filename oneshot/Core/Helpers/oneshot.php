<?php

use CodeIgniter\I18n\Time;

if (! function_exists('_oneshotKey')) {
    function _oneshotKey(): string
    {
        return env('app.secretKey', config('App')->secretKey ?? 'oneshot');
    }
}

if (! function_exists('encodeId')) {
    function encodeId(int $id): string
    {
        $key     = _oneshotKey();
        $payload = rtrim(strtr(base64_encode((string) $id), '+/', '-_'), '=');
        $check   = substr(md5($payload . ':' . $key), 0, 5);
        return $payload . '-' . $check;
    }
}

if (! function_exists('decodeId')) {
    function decodeId(string $hash): int
    {
        if (strpos($hash, '-') === false) {
            return 0;
        }

        $pos     = strrpos($hash, '-');
        $payload = substr($hash, 0, $pos);
        $check   = substr($hash, $pos + 1);
        $key     = _oneshotKey();

        if (! hash_equals(substr(md5($payload . ':' . $key), 0, 5), $check)) {
            return 0;
        }

        return (int) base64_decode(str_pad(strtr($payload, '-_', '+/'), strlen($payload) % 4, '=', STR_PAD_RIGHT));
    }
}

if (! function_exists('signId')) {
    function signId(int $id): string
    {
        return encodeId($id);
    }
}

if (! function_exists('signedId')) {
    function signedId(string $hash): int
    {
        return decodeId($hash);
    }
}

if (! function_exists('l')) {
    function l(mixed $data, string $tag = 'app'): void
    {
        $path = WRITEPATH . 'logs/' . $tag . '.log';
        $line = '[' . date('Y-m-d H:i:s') . '] ' . (is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE)) . PHP_EOL;
        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }
}

if (! function_exists('rds')) {
    function rds(string $key): bool
    {
        $file = WRITEPATH . 'cache/rds_' . md5($key) . '.lock';
        $fp   = fopen($file, 'c');

        if ($fp === false) {
            return false;
        }

        if (! flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            return false;
        }

        // Store handle in global so it stays open (lock held until process ends)
        $GLOBALS['_rds_handles'][$key] = $fp;
        return true;
    }
}

if (! function_exists('render')) {
    function render(string $view, array $data = [], array $options = []): string
    {
        if (! str_contains($view, '::')) {
            return view($view, $data, $options);
        }

        [$module, $path] = explode('::', $view, 2);

        $appPath = strtolower($module) . '/' . $path;
        if (is_file(APPPATH . 'Views/' . $appPath . '.php')) {
            return view($appPath, $data, $options);
        }

        if (is_file(ROOTPATH . 'modules/' . $module . '/Views/' . $path . '.php')) {
            return view('Modules\\' . $module . '\Views/' . $path, $data, $options);
        }

        return view('OneShot\\' . $module . '\Views/' . $path, $data, $options);
    }
}

if (! function_exists('option')) {
    /**
     * Get a global app setting.
     */
    function option(string $key, mixed $default = null): mixed
    {
        return (new \OneShot\Settings\Models\Setting())->fetch($key, $default, null);
    }
}

if (! function_exists('userOption')) {
    /**
     * Get a per-user setting. Resolves user_id from session automatically.
     */
    function userOption(string $key, mixed $default = null, ?int $userId = null): mixed
    {
        if ($userId === null) {
            $userId = (int) (\Config\Services::session()->get('user_id') ?? 0) ?: null;
        }

        if ($userId === null) {
            return $default;
        }

        return (new \OneShot\Settings\Models\Setting())->fetch($key, $default, $userId);
    }
}

if (! function_exists('setOption')) {
    /**
     * Save a setting. Pass $userId explicitly for user-scoped options; null = global.
     */
    function setOption(string $key, mixed $value, ?int $userId = null): void
    {
        (new \OneShot\Settings\Models\Setting())->store($key, $value, $userId);
    }
}

if (! function_exists('encrypt')) {
    function encrypt(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $key = hash('sha256', env('app.secretKey', 'oneshot'), true);
        $iv  = random_bytes(16);
        $enc = openssl_encrypt($value, 'AES-256-CTR', $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . $enc);
    }
}

if (! function_exists('decrypt')) {
    function decrypt(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $raw = base64_decode($value, true);

        if ($raw === false || strlen($raw) < 17) {
            return $value; // not encrypted — return as-is
        }

        $key = hash('sha256', env('app.secretKey', 'oneshot'), true);
        $iv  = substr($raw, 0, 16);
        $enc = substr($raw, 16);

        $dec = openssl_decrypt($enc, 'AES-256-CTR', $key, OPENSSL_RAW_DATA, $iv);

        return $dec !== false ? $dec : $value;
    }
}

if (! function_exists('__')) {
    function __(string $key, string $default = ''): string
    {
        [$file, $line] = array_pad(explode('.', $key, 2), 2, '');
        $lang = \Config\Services::language();

        try {
            $val = $lang->getLine($key);
        } catch (\Throwable $e) {
            $val = false;
        }

        return ($val && $val !== $key) ? $val : $default;
    }
}
