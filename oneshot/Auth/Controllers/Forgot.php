<?php

namespace OneShot\Auth\Controllers;

use OneShot\Auth\Models\User;
use OneShot\Auth\Models\Token;
use OneShot\Auth\Services\MailService;

class Forgot extends Auth
{
    public function index(): string
    {
        $this->appendBC(__('auth.forgot_title', 'Reset Password'), '');
        return $this->render('Auth::front/forgot');
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $throttler = service('throttler');
        $key = 'forgot_' . md5($this->request->getIPAddress());

        if (! $throttler->check($key, 3, MINUTE * 10)) {
            return $this->redirectWith(
                route_to('auth.forgot'),
                __('auth.too_many_attempts', 'Too many attempts. Please try again later.'),
                'error'
            );
        }

        $email = mb_strtolower(trim($this->request->getPost('email') ?? ''));
        $user  = (new User())->findByEmail($email);

        // Always the same response — prevent email enumeration
        // Always perform an equivalent operation to equalise timing
        if ($user !== null && $user->status === 'active') {
            $token = (new Token())->create($user->id, 'reset_password');
            (new MailService())->sendPasswordReset($user, $token);
        } else {
            // Equalise timing: do a dummy hash operation
            hash('sha256', $email . bin2hex(random_bytes(8)));
        }

        return $this->redirectWith(
            route_to('auth.forgot'),
            __('auth.forgot_sent', 'If that email is registered, a reset link is on its way.'),
            'success'
        );
    }
}
