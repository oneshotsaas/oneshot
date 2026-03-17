<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingProviderRefsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'entity_id'   => ['type' => 'INT', 'unsigned' => true],
            'provider'    => ['type' => 'VARCHAR', 'constraint' => 30],
            'ref_id'      => ['type' => 'VARCHAR', 'constraint' => 128],
            'meta'        => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['entity_type', 'entity_id', 'provider']);
        $this->forge->addKey(['provider', 'ref_id']);
        $this->forge->createTable('billing_provider_refs');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_provider_refs');
    }
}
