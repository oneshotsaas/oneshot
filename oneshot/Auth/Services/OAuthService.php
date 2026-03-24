<?php

namespace OneShot\Auth\Services;

use OneShot\Auth\Config\OAuth as OAuthConfig;

/**
 * OAuth 2.0 service — no external libraries.
 * All HTTP requests via CI4 service('curlrequest').
 * Credentials read from option() with fallback to env().
 */
class OAuthService
{
    private array $providers;

    public function __construct()
    {
        $this->providers = OAuthConfig::providers();
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Build the authorization URL and store state+provider in session.
     */
    public function getAuthUrl(string $provider): string
    {
        $cfg      = $this->config($provider);
        $creds    = $this->credentials($provider);
        $state    = bin2hex(random_bytes(16));
        $redirect = $this->redirectUri($provider);

        session()->set([
            'oauth_state'      => $state,
            'oauth_state_at'   => time(),
            'oauth_provider'   => $provider,
        ]);

        $params = [
            'client_id'     => $creds['id'],
            'redirect_uri'  => $redirect,
            'response_type' => 'code',
            'scope'         => $cfg['scope'],
            'state'         => $state,
        ];

        // Apple requires additional params
        if ($provider === 'apple') {
            $params['response_mode'] = 'form_post';
        }

        return $cfg['auth_url'] . '?' . http_build_query($params);
    }

    /**
     * Handle OAuth callback. Returns normalized user object.
     * Validates state, exchanges code for token, fetches user info.
     *
     * @throws \RuntimeException on any validation or API failure
     */
    public function handleCallback(string $provider, string $code, string $state): object
    {
        $this->validateState($provider, $state);

        $accessToken = $this->exchangeCode($provider, $code);

        return $this->fetchUser($provider, $accessToken);
    }

    /**
     * Returns list of enabled provider keys (enabled=1 AND credentials set).
     */
    public function enabledProviders(): array
    {
        $enabled = [];

        foreach (array_keys($this->providers) as $name) {
            if (! $this->isEnabled($name)) {
                continue;
            }
            $creds = $this->credentials($name);
            if (! empty($creds['id'])) {
                $enabled[] = $name;
            }
        }

        return $enabled;
    }

    /**
     * Whether a provider is toggled on in Settings.
     */
    public function isEnabled(string $provider): bool
    {
        return (bool) option("auth.oauth_{$provider}_enabled", '0');
    }

    // ------------------------------------------------------------------
    // State validation
    // ------------------------------------------------------------------

    private function validateState(string $provider, string $state): void
    {
        $storedProvider = session('oauth_provider');
        $storedState    = session('oauth_state');
        $storedAt       = session('oauth_state_at');

        // Always clear — one-time use
        session()->remove(['oauth_state', 'oauth_state_at', 'oauth_provider']);

        if ($provider !== $storedProvider) {
            throw new \RuntimeException('OAuth provider mismatch.');
        }

        if (! hash_equals((string) $storedState, $state)) {
            throw new \RuntimeException('OAuth state mismatch.');
        }

        if ((time() - (int) $storedAt) > 600) {
            throw new \RuntimeException('OAuth state expired.');
        }
    }

    // ------------------------------------------------------------------
    // Token exchange
    // ------------------------------------------------------------------

    private function exchangeCode(string $provider, string $code): string
    {
        $cfg     = $this->config($provider);
        $creds   = $this->credentials($provider);
        $secret  = $provider === 'apple'
            ? $this->buildAppleClientSecret($creds)
            : $creds['secret'];

        $params = [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $this->redirectUri($provider),
            'client_id'    => $creds['id'],
            'client_secret'=> $secret,
        ];

        $headers = ['Accept' => 'application/json'];
        if ($provider === 'github') {
            $headers['Accept'] = 'application/json';
        }

        $response = service('curlrequest')->request('POST', $cfg['token_url'], [
            'form_params' => $params,
            'headers'     => $headers,
        ]);

        $data = json_decode($response->getBody(), true);

        if (empty($data['access_token'])) {
            l(['event' => 'oauth_token_error', 'provider' => $provider, 'response' => $data], 'auth_oauth');
            throw new \RuntimeException('Failed to get access token from ' . $provider);
        }

        // Apple: also return id_token for user info parsing
        if ($provider === 'apple' && ! empty($data['id_token'])) {
            return $data['access_token'] . '|apple_id_token|' . $data['id_token'];
        }

        return $data['access_token'];
    }

    // ------------------------------------------------------------------
    // User info
    // ------------------------------------------------------------------

    private function fetchUser(string $provider, string $accessToken): object
    {
        return match ($provider) {
            'apple'     => $this->fetchAppleUser($accessToken),
            'github'    => $this->fetchGithubUser($accessToken),
            'facebook'  => $this->fetchFacebookUser($accessToken),
            default     => $this->fetchStandardUser($provider, $accessToken),
        };
    }

    private function fetchStandardUser(string $provider, string $accessToken): object
    {
        $cfg      = $this->config($provider);
        $response = service('curlrequest')->request('GET', $cfg['userinfo_url'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return $this->normalizeUser($provider, $data);
    }

    private function fetchGithubUser(string $accessToken): object
    {
        $cfg = $this->config('github');

        // Basic profile
        $response = service('curlrequest')->request('GET', $cfg['userinfo_url'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
                'User-Agent'    => 'OneShot-Auth',
            ],
        ]);
        $profile = json_decode($response->getBody(), true);

        // GitHub may not return email in profile — fetch separately
        if (empty($profile['email'])) {
            $emailsResponse = service('curlrequest')->request('GET', $cfg['emails_url'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                    'User-Agent'    => 'OneShot-Auth',
                ],
            ]);
            $emails = json_decode($emailsResponse->getBody(), true);
            foreach ($emails as $e) {
                if (! empty($e['primary']) && ! empty($e['verified'])) {
                    $profile['email']          = $e['email'];
                    $profile['email_verified'] = true;
                    break;
                }
            }
        }

        return $this->normalizeUser('github', $profile);
    }

    private function fetchFacebookUser(string $accessToken): object
    {
        $cfg      = $this->config('facebook');
        $response = service('curlrequest')->request('GET', $cfg['userinfo_url'], [
            'query'   => ['access_token' => $accessToken],
            'headers' => ['Accept' => 'application/json'],
        ]);
        $data = json_decode($response->getBody(), true);

        return $this->normalizeUser('facebook', $data);
    }

    private function fetchAppleUser(string $accessToken): object
    {
        // Apple returns user info in id_token (JWT payload, no signature verification needed for id fields)
        [, $idToken] = explode('|apple_id_token|', $accessToken . '|apple_id_token|', 2);

        if (empty($idToken)) {
            throw new \RuntimeException('Apple id_token missing.');
        }

        $parts   = explode('.', $idToken);
        $payload = json_decode(base64_decode(strtr($parts[1] ?? '', '-_', '+/')), true);

        return $this->normalizeUser('apple', $payload);
    }

    // ------------------------------------------------------------------
    // Normalize to common format
    // ------------------------------------------------------------------

    private function normalizeUser(string $provider, array $data): object
    {
        return (object) [
            'id'             => (string) ($data['id'] ?? $data['sub'] ?? ''),
            'name'           => $data['name'] ?? trim(($data['given_name'] ?? '') . ' ' . ($data['family_name'] ?? '')),
            'email'          => isset($data['email']) ? mb_strtolower(trim($data['email'])) : null,
            'email_verified' => match ($provider) {
                'google', 'microsoft', 'linkedin' => true,
                'github'   => (bool) ($data['email_verified'] ?? false),
                'facebook' => (bool) ($data['email_verified'] ?? false),
                'apple'    => ! empty($data['email']), // first-login only, treat as verified
                default    => false,
            },
            'provider' => $provider,
        ];
    }

    // ------------------------------------------------------------------
    // Apple JWT client_secret
    // ------------------------------------------------------------------

    private function buildAppleClientSecret(array $creds): string
    {
        $teamId    = $creds['team_id']   ?? option('auth.oauth_apple_team_id',   env('OAUTH_APPLE_TEAM_ID', ''));
        $keyId     = $creds['key_id']    ?? option('auth.oauth_apple_key_id',    env('OAUTH_APPLE_KEY_ID', ''));
        $serviceId = $creds['id'];
        $privateKey= $creds['secret'];   // PEM private key stored in settings

        $now = time();

        $header  = base64_encode(json_encode(['alg' => 'ES256', 'kid' => $keyId]));
        $payload = base64_encode(json_encode([
            'iss' => $teamId,
            'iat' => $now,
            'exp' => $now + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $serviceId,
        ]));

        $header  = strtr($header, '+/', '-_');
        $payload = strtr($payload, '+/', '-_');
        $data    = $header . '.' . $payload;

        $key = openssl_pkey_get_private($privateKey);
        openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256);

        $sig = strtr(base64_encode($signature), '+/', '-_');

        return $data . '.' . $sig;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function config(string $provider): array
    {
        if (! isset($this->providers[$provider])) {
            throw new \InvalidArgumentException("Unknown OAuth provider: {$provider}");
        }

        return $this->providers[$provider];
    }

    private function credentials(string $provider): array
    {
        $id     = option("auth.oauth_{$provider}_id",     env('OAUTH_' . strtoupper($provider) . '_ID',     ''));
        $secret = option("auth.oauth_{$provider}_secret", env('OAUTH_' . strtoupper($provider) . '_SECRET', ''));

        return compact('id', 'secret');
    }

    private function redirectUri(string $provider): string
    {
        return self::callbackUrl($provider);
    }

    // ------------------------------------------------------------------
    // Callback URL helpers (public — used by Settings UI and views)
    // ------------------------------------------------------------------

    /**
     * Derive a per-installation, per-provider secret from the app key.
     * 16 hex chars — unique per project, different per provider.
     */
    public static function providerSecret(string $provider): string
    {
        $appKey = config('App')->secretKey ?? 'oneshot';
        return substr(hash_hmac('sha256', 'oauth:' . $provider, $appKey), 0, 16);
    }

    /**
     * Full callback URL including the provider secret segment.
     * /auth/oauth/{provider}/{secret}/callback
     */
    public static function callbackUrl(string $provider): string
    {
        return site_url('auth/oauth/' . $provider . '/' . self::providerSecret($provider) . '/callback');
    }
}
