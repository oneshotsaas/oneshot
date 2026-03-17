<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'key'     => ['type' => 'VARCHAR', 'constraint' => 128],
            'value'   => ['type' => 'TEXT', 'null' => true],
            'type'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'text'],
            'options' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'label'   => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'sort'    => ['type' => 'TINYINT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['key', 'user_id']);
        $this->forge->createTable('settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('settings');
    }
}
