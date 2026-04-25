<?php

namespace OneShot\Users\Controllers\Admin;

use OneShot\Core\Controllers\Admin;
use OneShot\Auth\Models\User;

class Users extends Admin
{
    private User $users;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->users = new User();
    }

    public function index(): string
    {
        $this->appendBC(__('users.users', 'Users'), route_to('admin.users'));
        return $this->render('Users::admin/index', [
            'users' => $this->users->getAll(),
        ]);
    }

    public function show(string $hash): string
    {
        $id   = signedId($hash);
        $user = $this->users->getById($id);

        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->appendBC(__('users.users', 'Users'), route_to('admin.users'));
        $this->appendBC($user->name ?? $user->email, '');
        return $this->render('Users::admin/show', ['user' => $user]);
    }
}
