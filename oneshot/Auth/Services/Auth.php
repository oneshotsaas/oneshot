<?php

namespace OneShot\Auth\Services;

use OneShot\Core\Services\Base;
use OneShot\Auth\Models\User;
use CodeIgniter\Events\Events;

class Auth extends Base
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        // Always run password_verify to prevent timing-based email enumeration
        $hash = $user?->password ?? '$2y$10$usesomesillystringfore2uDLvp1Ii2e./U9C8aNi2nlHsn9X0xfke';
        if (! password_verify($password, $hash) || $user === null) {
            return ['success' => false, 'error' => __('auth.invalid_credentials', 'Invalid email or password.')];
        }

        if (($user->status ?? 'active') !== 'active') {
            return ['success' => false, 'error' => __('auth.account_inactive', 'Account is inactive.')];
        }

        session()->set([
            'user_id'   => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
            'user_lang' => $user->lang ?? 'en',
        ]);

        Events::trigger('user.login', $user);

        return ['success' => true, 'user' => $user];
    }

    public function register(array $data): array
    {
        if ($this->users->findByEmail($data['email']) !== null) {
            return ['success' => false, 'error' => __('auth.email_taken', 'Email already in use.')];
        }

        if (strlen($data['password']) < 8) {
            return ['success' => false, 'error' => __('auth.password_too_short', 'Password must be at least 8 characters.')];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['role']     = $data['role'] ?? 'user';
        $data['status']   = 'active';

        $user = $this->users->addGet($data);

        Events::trigger('user.registered', $user);

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        session()->destroy();
    }

    public function user(): object|null
    {
        $id = session()->get('user_id');
        if (! $id) {
            return null;
        }
        return $this->users->getById((int) $id);
    }
}
