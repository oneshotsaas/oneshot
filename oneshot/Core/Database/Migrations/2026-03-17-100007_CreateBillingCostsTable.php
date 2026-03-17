<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingCostsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'action'       => ['type' => 'VARCHAR', 'constraint' => 128],
            'label'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'unit_type'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'cost_per_unit'=> ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => '0.0000'],
            'meta'         => ['type' => 'TEXT', 'null' => true],
            'meta_version' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('action');
        $this->forge->createTable('billing_costs');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_costs');
    }
}
