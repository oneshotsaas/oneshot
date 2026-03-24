<?php

namespace OneShot\Auth\Controllers;

use OneShot\Auth\Services\Auth as AuthService;
use OneShot\Auth\Services\OAuthService;

class OAuth extends Auth
{
    private OAuthService $oauth;
    private AuthService  $auth;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->oauth = new OAuthService();
        $this->auth  = new AuthService();
    }

    /**
     * Redirect to OAuth provider's authorization URL.
     */
    public function redirect(string $provider): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->oauth->isEnabled($provider)) {
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        try {
            $url = $this->oauth->getAuthUrl($provider);
        } catch (\Throwable $e) {
            l(['event' => 'oauth_redirect_error', 'provider' => $provider, 'error' => $e->getMessage()], 'auth_oauth');
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        return redirect()->to($url);
    }

    /**
     * Handle OAuth provider callback.
     */
    public function callback(string $provider, string $secret): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! hash_equals(OAuthService::providerSecret($provider), $secret)) {
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        $code  = $this->request->getGet('code')  ?? '';
        $state = $this->request->getGet('state') ?? '';
        $error = $this->request->getGet('error') ?? '';

        if ($error || ! $code) {
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        try {
            $providerUser = $this->oauth->handleCallback($provider, $code, $state);
            $result       = $this->auth->loginWithOAuth($provider, $providerUser);
        } catch (\Throwable $e) {
            l(['event' => 'oauth_callback_error', 'provider' => $provider, 'error' => $e->getMessage()], 'auth_oauth');
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        if (! $result['success']) {
            return $this->redirectWith(route_to('auth.login'), $result['error'], 'error');
        }

        return redirect()->to(config('Prefixes')->app . '/');
    }

    /**
     * Telegram Login Widget callback (POST).
     * https://core.telegram.org/widgets/login
     */
    public function telegram(string $provider, string $secret): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! hash_equals(OAuthService::providerSecret('telegram'), $secret)) {
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        $throttler = service('throttler');
        $key = 'telegram_' . md5($this->request->getIPAddress());

        if (! $throttler->check($key, 10, MINUTE)) {
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Too many requests.'), 'error');
        }

        if (! $this->oauth->isEnabled('telegram')) {
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed.'), 'error');
        }

        $data = $this->request->getPost();
        if (empty($data['id']) || empty($data['hash'])) {
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Invalid Telegram data.'), 'error');
        }

        if (! $this->verifyTelegram($data)) {
            l(['event' => 'telegram_auth_failed', 'ip' => $this->request->getIPAddress()], 'auth_oauth');
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        $providerUser = (object) [
            'id'             => (string) $data['id'],
            'name'           => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'email'          => null,
            'email_verified' => false,
            'provider'       => 'telegram',
        ];

        try {
            $result = $this->auth->loginWithOAuth('telegram', $providerUser);
        } catch (\Throwable $e) {
            l(['event' => 'telegram_login_error', 'error' => $e->getMessage()], 'auth_oauth');
            return $this->redirectWith(route_to('auth.login'), __('auth.oauth_error', 'Sign-in failed. Please try again.'), 'error');
        }

        if (! $result['success']) {
            return $this->redirectWith(route_to('auth.login'), $result['error'], 'error');
        }

        return redirect()->to(config('Prefixes')->app . '/');
    }

    // ------------------------------------------------------------------
    // Telegram verification
    // ------------------------------------------------------------------

    private function verifyTelegram(array $data): bool
    {
        $hash    = $data['hash'];
        $authDate= (int) ($data['auth_date'] ?? 0);

        // auth_date must be within 120 seconds
        if ((time() - $authDate) > 120) {
            return false;
        }

        // Remove hash before building check string
        unset($data['hash']);
        ksort($data);

        $checkString = implode("\n", array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($data),
            array_values($data)
        ));

        $botToken   = option('auth.oauth_telegram_bot_token', env('OAUTH_TELEGRAM_BOT_TOKEN', ''));
        $secretKey  = hash('sha256', $botToken, true);
        $computed   = hash_hmac('sha256', $checkString, $secretKey);

        return hash_equals($computed, $hash);
    }
}
