<?php

namespace OneShot\Auth\Controllers;

use OneShot\Auth\Services\Auth as AuthService;

class Register extends Auth
{
    public function index(): string
    {
        $this->appendBC(__('auth.register', 'Register'), '');
        return $this->render('Auth::front/register');
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

        return redirect()->to(config('Prefixes')->app . '/');
    }
}
