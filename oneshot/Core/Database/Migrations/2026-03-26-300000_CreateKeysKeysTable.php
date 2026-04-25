<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKeysKeysTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'unsigned' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'key_id'          => ['type' => 'CHAR', 'constraint' => 14, 'null' => false],
            'key_hash'        => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'key_suffix'      => ['type' => 'CHAR', 'constraint' => 4, 'null' => true],
            'expires_at'      => ['type' => 'DATETIME', 'null' => true],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'limits_requests' => ['type' => 'TEXT', 'null' => true],
            'limits_credits'  => ['type' => 'TEXT', 'null' => true],
            'last_used_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('key_id');
        $this->forge->addUniqueKey('key_hash');
        $this->forge->addForeignKey('user_id', 'auth_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('keys_keys');
    }

    public function down(): void
    {
        $this->forge->dropTable('keys_keys');
    }
}
