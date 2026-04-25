<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKeysUsageTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'key_id'     => ['type' => 'INT', 'unsigned' => true],
            'usage_date' => ['type' => 'DATETIME', 'null' => true],
            'requests'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'credits'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['key_id', 'usage_date']);
        $this->forge->addForeignKey('key_id', 'keys_keys', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('keys_usage');
    }

    public function down(): void
    {
        $this->forge->dropTable('keys_usage');
    }
}
