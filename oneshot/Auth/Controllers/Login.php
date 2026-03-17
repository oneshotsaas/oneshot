<?php

namespace OneShot\Auth\Controllers;

use OneShot\Auth\Services\Auth as AuthService;

class Login extends Auth
{
    public function index(): string
    {
        $this->appendBC(__('auth.login', 'Login'), '');
        return $this->render('Auth::front/login');
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $throttler = service('throttler');
        $key = 'login_' . md5($this->request->getIPAddress());

        if (! $throttler->check($key, 10, MINUTE * 10)) {
            return $this->redirectWith(
                route_to('auth.login'),
                __('auth.too_many_attempts', 'Too many login attempts. Please try again in a few minutes.'),
                'error'
            );
        }

        $result = (new AuthService())->login(
            $this->request->getPost('email'),
            $this->request->getPost('password')
        );

        if (! $result['success']) {
            return $this->redirectWith(
                route_to('auth.login'),
                $result['error'],
                'error'
            );
        }

        $throttler->remove($key);

        return redirect()->to(config('Prefixes')->app . '/');
    }

    public function destroy(): \CodeIgniter\HTTP\RedirectResponse
    {
        (new AuthService())->logout();
        return redirect()->to('/');
    }
}
