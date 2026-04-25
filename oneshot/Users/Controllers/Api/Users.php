<?php

namespace OneShot\Users\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use OneShot\Core\Controllers\Api;
use OneShot\Auth\Models\User;
use OneShot\Auth\Services\Auth as AuthService;
use Providers\Telegram\Telegram;

class Users extends Api
{
    protected string $modelClass = User::class;
    protected string $resource   = 'user';
    protected string $resources  = 'users';

    // password excluded intentionally — never expose hashes
    protected array $fields = ['id', 'name', 'email', 'role', 'status', 'lang', 'timezone', 'telegram_id', 'email_verified_at', 'created_at', 'updated_at'];

    // role excluded intentionally — privilege escalation risk; change role via admin UI only
    protected array $editable  = ['name', 'telegram_id', 'status', 'lang', 'timezone'];
    protected array $creatable = ['name', 'lang', 'timezone', 'telegram_id'];

    public function store(): ResponseInterface
    {
        $input    = $this->request->getJSON(true) ?: $this->request->getPost();
        $email    = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if ($email === '' || $password === '') {
            return $this->fail(__('users.email_password_required', 'Email and password are required.'), 400);
        }

        $data = ['email' => $email, 'password' => $password];

        foreach ($this->creatable as $field) {
            if (!empty($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        // send_verification: false — user was not self-registering, no email needed
        // status: active — bypass pending state regardless of email_verification setting
        $result = (new AuthService())->register($data, ['send_verification' => false, 'status' => 'active']);

        if (!$result['success']) {
            return $this->fail($result['error'], 400);
        }

        $user = $result['user'];

		return $this->ok([$this->resource => $this->serialize($user)], 201);
    }

    public function delete(int $id): ResponseInterface
    {
        $user = $this->model()->getById($id);

        if ($user === null) {
            return $this->fail(__('users.not_found', 'User not found.'), 404);
        }

        // admins cannot be deleted via API — change their role first via admin UI
        if ($user->role === 'admin') {
            return $this->fail(__('users.cannot_delete_admin', 'Admin users cannot be deleted via API.'), 403);
        }

        // Anonymise PII before soft-delete:
        //   - email freed so the address can be re-registered
        //   - deleted_email_hash kept for fraud detection on re-registration
        $this->model()->update($id, [
            'email'              => 'deleted_' . $id . '@deleted',
            'deleted_email_hash' => hash('sha256', mb_strtolower(trim($user->email))),
            'name'               => null,
            'password'           => '',
            'telegram_id'        => null,
            'email_verified_at'  => null,
        ]);

        $this->model()->delete($id);

        return $this->ok();
    }
}
