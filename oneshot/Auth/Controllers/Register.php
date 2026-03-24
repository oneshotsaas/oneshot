<?php

namespace OneShot\Auth\Controllers;

use OneShot\Auth\Services\Auth as AuthService;
use OneShot\Auth\Services\OAuthService;

class Register extends Auth
{
    public function index(): string
    {
        $this->appendBC(__('auth.register', 'Register'), '');
        return $this->render('Auth::front/register', [
            'oauthProviders' => (new OAuthService())->enabledProviders(),
            'passwordPolicy' => AuthService::passwordPolicy(),
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $result = (new AuthService())->register([
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'name'     => $this->request->getPost('name'),
        ]);

        if (! $result['success']) {
            return $this->redirectWith(
                route_to('auth.register'),
                $result['error'],
                'error'
            );
        }

        $verification = option('auth.email_verification', 'disabled');

        if ($verification === 'required') {
            return $this->redirectWith(
                route_to('auth.login'),
                __('auth.verify_title', 'Check your inbox'),
                'info'
            );
        }

        return redirect()->to(config('Prefixes')->app . '/');
    }
}
