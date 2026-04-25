<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'url'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'data'       => ['type' => 'TEXT', 'null' => true],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'read_at']);
        $this->forge->createTable('notifications');
    }

    public function down(): void
    {
        $this->forge->dropTable('notifications');
    }
}
