<?php

namespace OneShot\Users\Controllers\App;

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
        $userId = (int) session()->get('user_id');
        $user   = $this->users->getById($userId);
        $this->appendBC(__('users.profile', 'Profile'), route_to('app.profile'));

        $notifier = new \OneShot\Notifications\Services\Notifier();

        return $this->render('Users::app/profile', [
            'user'          => $user,
            'userThemeMode' => userOption('appearance.mode', 'dark', $userId),
            'notifPrefs'    => $notifier->getPreferences($userId, 'user'),
            'notifTypes'    => config('NotificationTypes'),
        ]);
    }

    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = (int) session()->get('user_id');
        $data = ['name' => $this->request->getPost('name')];

        $telegramId = trim($this->request->getPost('telegram_id') ?? '');
        if ($telegramId !== '') {
            $data['telegram_id'] = $telegramId;
        } elseif ($this->request->getPost('telegram_id') !== null) {
            $data['telegram_id'] = null;
        }

        $this->users->save(array_merge($data, ['id' => $id]));

        $mode = $this->request->getPost('theme_mode') === 'light' ? 'light' : 'dark';
        setOption('appearance.mode', $mode, $id);

        return $this->redirectWith(route_to('app.profile'), __('users.profile_updated', 'Profile updated.'));
    }
}
