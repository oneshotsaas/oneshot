<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTasksTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'type'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'payload'      => ['type' => 'TEXT', 'null' => false],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'pending'],
            'attempts'     => ['type' => 'TINYINT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'max_attempts' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => false, 'default' => 3],
            'error'        => ['type' => 'TEXT', 'null' => true],
            'retry_after'  => ['type' => 'DATETIME', 'null' => true],
            'claimed_at'   => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['status', 'retry_after']);
        $this->forge->addKey('claimed_at');
        $this->forge->createTable('tasks');
    }

    public function down(): void
    {
        $this->forge->dropTable('tasks');
    }
}
