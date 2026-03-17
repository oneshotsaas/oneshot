<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 191, 'unique' => true],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'role'       => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'user'],
            'lang'       => ['type' => 'VARCHAR', 'constraint' => 2,   'default' => 'en'],
            'timezone'   => ['type' => 'VARCHAR', 'constraint' => 64,  'default' => 'UTC'],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20,  'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('auth_users');
    }

    public function down(): void
    {
        $this->forge->dropTable('auth_users');
    }
}
