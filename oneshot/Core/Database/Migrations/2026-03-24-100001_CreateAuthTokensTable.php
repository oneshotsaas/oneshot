<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTokensTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 32],
            'token'      => ['type' => 'CHAR', 'constraint' => 64, 'unique' => true],
            'payload'    => ['type' => 'VARCHAR', 'constraint' => 1000, 'null' => true],
            'expires_at' => ['type' => 'DATETIME'],
            'used_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'auth_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['user_id', 'type']);
        $this->forge->addKey('expires_at');
        $this->forge->createTable('auth_tokens');
    }

    public function down(): void
    {
        $this->forge->dropTable('auth_tokens');
    }
}
