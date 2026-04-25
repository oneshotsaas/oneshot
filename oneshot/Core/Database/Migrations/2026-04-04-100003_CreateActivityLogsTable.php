<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'subject_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'subject_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'metadata'     => ['type' => 'TEXT', 'null' => true],
            'ip'           => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('action');
        $this->forge->addKey('created_at');
        $this->forge->createTable('activity_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('activity_logs');
    }
}
