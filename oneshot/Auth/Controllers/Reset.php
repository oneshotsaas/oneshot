<?php

namespace OneShot\Auth\Controllers;

use OneShot\Auth\Models\Token;
use OneShot\Auth\Services\Auth as AuthService;

class Reset extends Auth
{
    public function index(string $rawToken): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $throttler = service('throttler');
        $key = 'reset_' . md5($this->request->getIPAddress());

        if (! $throttler->check($key, 30, MINUTE)) {
            return $this->redirectWith(route_to('auth.forgot'), __('auth.too_many_attempts', 'Too many attempts.'), 'error');
        }

        $token = (new Token())->consume($rawToken, 'reset_password');

        if ($token === null) {
            return $this->redirectWith(
                route_to('auth.forgot'),
                __('auth.reset_expired', 'This link has expired. Please request a new one.'),
                'error'
            );
        }

        // Store token data in session for POST step
        session()->set('reset_user_id', $token->user_id);
        session()->set('reset_confirmed', true);

        $this->appendBC(__('auth.reset_title', 'Set New Password'), '');
        return $this->render('Auth::front/reset', ['passwordPolicy' => AuthService::passwordPolicy()]);
    }

    public function store(string $rawToken): \CodeIgniter\HTTP\RedirectResponse
    {
        // Verify the session confirmation set in GET step
        if (! session('reset_confirmed')) {
            return $this->redirectWith(route_to('auth.forgot'), __('auth.reset_expired', 'Session expired. Please request a new link.'), 'error');
        }

        $userId   = (int) session('reset_user_id');
        $password = $this->request->getPost('password') ?? '';
        $confirm  = $this->request->getPost('confirm_password') ?? '';

        if ($password !== $confirm) {
            return $this->redirectWith(
                current_url(),
                __('auth.passwords_mismatch', 'Passwords do not match.'),
                'error'
            );
        }

        session()->remove(['reset_user_id', 'reset_confirmed']);

        $result = (new AuthService())->resetPassword($userId, $password);

        if (! $result['success']) {
            return $this->redirectWith(current_url(), $result['error'], 'error');
        }

        return $this->redirectWith(
            route_to('auth.login'),
            __('auth.reset_success', 'Password updated. You can now sign in.'),
            'success'
        );
    }
}
