<?php

namespace OneShot\Users\Controllers;

use OneShot\Core\Controllers\App;
use OneShot\Auth\Models\User;

class Profile extends App
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
        $user = $this->users->getById((int) session()->get('user_id'));
        $this->appendBC(__('users.profile', 'Profile'), route_to('app.profile'));

        return $this->render('Users::app/profile', [
            'user'           => $user,
            'userThemeMode'  => userOption('appearance.mode', 'dark', (int) session()->get('user_id')),
        ]);
    }

    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = (int) session()->get('user_id');
        $data = ['name' => $this->request->getPost('name')];

        $this->users->save(array_merge($data, ['id' => $id]));

        $mode = $this->request->getPost('theme_mode') === 'light' ? 'light' : 'dark';
        setOption('appearance.mode', $mode, $id);

        return $this->redirectWith(route_to('app.profile'), __('users.profile_updated', 'Profile updated.'));
    }
}
