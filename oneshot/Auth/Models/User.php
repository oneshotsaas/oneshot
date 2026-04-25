<?php

namespace OneShot\Auth\Models;

use OneShot\Core\Models\Base;

class User extends Base
{
    protected $table         = 'auth_users';
    protected $allowedFields = ['email', 'deleted_email_hash', 'email_verified_at', 'password', 'role', 'name', 'lang', 'timezone', 'status', 'telegram_id'];

    public function findByEmail(string $email): object|null
    {
        return $this->where('email', $email)->limit(1)->first() ?: null;
    }
}
