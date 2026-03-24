<?php

namespace OneShot\Auth\Controllers;

use OneShot\Auth\Models\Token;
use OneShot\Auth\Models\User;
use OneShot\Auth\Services\MailService;

class Verify extends Auth
{
    public function index(string $rawToken): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $throttler = service('throttler');
        $key = 'verify_' . md5($this->request->getIPAddress());

        if (! $throttler->check($key, 30, MINUTE)) {
            return $this->redirectWith(route_to('auth.login'), __('auth.too_many_attempts', 'Too many attempts.'), 'error');
        }

        $token = (new Token())->consume($rawToken, 'verify_email');

        if ($token === null) {
            // If already logged in and verified — show friendly message
            $currentUserId = session('user_id');
            if ($currentUserId) {
                $user = (new User())->getById($currentUserId);
                if ($user && ! empty($user->email_verified_at)) {
                    return $this->redirectWith(
                        config('Prefixes')->app . '/',
                        __('auth.verify_success', 'Email confirmed! You\'re now signed in.'),
                        'success'
                    );
                }
            }

            // For everyone else — single generic message
            return $this->redirectWith(
                route_to('auth.login'),
                __('auth.verify_link_invalid', 'Link is invalid or expired.'),
                'error'
            );
        }

        // Mark email as verified
        $users = new User();
        $users->update($token->user_id, [
            'email_verified_at' => date('Y-m-d H:i:s'),
            'status'            => 'active',
        ]);

        // Auto-login
        $user = $users->getById($token->user_id);
        session()->regenerate(true);
        session()->set([
            'user_id'   => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
            'user_lang' => $user->lang ?? 'en',
        ]);

        return $this->redirectWith(
            config('Prefixes')->app . '/',
            __('auth.verify_success', 'Email confirmed! You\'re now signed in.'),
            'success'
        );
    }

    public function resend(): \CodeIgniter\HTTP\RedirectResponse
    {
        $throttler = service('throttler');
        $key = 'resend_' . md5($this->request->getIPAddress() . ($this->request->getPost('email') ?? ''));

        if (! $throttler->check($key, 3, HOUR)) {
            return $this->redirectWith(
                route_to('auth.login'),
                __('auth.too_many_attempts', 'Too many attempts. Please try again later.'),
                'error'
            );
        }

        $email = mb_strtolower(trim($this->request->getPost('email') ?? ''));
        $user  = (new User())->findByEmail($email);

        if ($user !== null && empty($user->email_verified_at)) {
            // Backend cooldown: last token must be > 60 seconds old
            $lastToken = (new Token())->lastCreated($user->id, 'verify_email');
            $cooldown  = 60;

            if ($lastToken === null || (time() - strtotime($lastToken->created_at)) >= $cooldown) {
                $token = (new Token())->create($user->id, 'verify_email');
                (new MailService())->sendVerification($user, $token);
            }
        }

        // Always same response
        return $this->redirectWith(
            route_to('auth.login'),
            __('auth.verify_resent', 'A new confirmation email has been sent.'),
            'info'
        );
    }
}
