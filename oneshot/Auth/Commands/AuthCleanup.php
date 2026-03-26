<?php

namespace OneShot\Auth\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OneShot\Auth\Models\Token;

class AuthCleanup extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:cleanup';
    protected $description = 'Delete expired and used auth tokens from auth_tokens table';

    public function run(array $params): void
    {
        $deleted = (new Token())->cleanup();
        CLI::write("Auth cleanup: {$deleted} token(s) removed.", 'green');
    }
}
