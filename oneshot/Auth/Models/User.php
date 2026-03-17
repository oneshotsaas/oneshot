<?php

namespace OneShot\Auth\Models;

use OneShot\Core\Models\Base;

class User extends Base
{
    protected $table         = 'auth_users';
    protected $allowedFields = ['email', 'password', 'role', 'name', 'lang', 'timezone', 'status'];

    public function findByEmail(string $email): object|null
    {
        return $this->where('email', $email)->limit(1)->first() ?: null;
    }
}
