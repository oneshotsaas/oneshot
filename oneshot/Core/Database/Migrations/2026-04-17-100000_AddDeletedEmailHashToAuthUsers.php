<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedEmailHashToAuthUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('auth_users', [
            'deleted_email_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'after'      => 'email',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('auth_users', 'deleted_email_hash');
    }
}
