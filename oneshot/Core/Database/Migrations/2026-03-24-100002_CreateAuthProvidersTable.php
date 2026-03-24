<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthProvidersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'unsigned' => true],
            'provider'       => ['type' => 'VARCHAR', 'constraint' => 32],
            'provider_id'    => ['type' => 'VARCHAR', 'constraint' => 128],
            'provider_email' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'auth_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['provider', 'provider_id']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('auth_providers');
    }

    public function down(): void
    {
        $this->forge->dropTable('auth_providers');
    }
}
