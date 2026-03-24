<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailVerifiedAtToAuthUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('auth_users', [
            'email_verified_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'email',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('auth_users', 'email_verified_at');
    }
}
