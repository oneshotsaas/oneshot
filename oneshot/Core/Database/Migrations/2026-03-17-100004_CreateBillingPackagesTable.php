<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingPackagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 128],
            'credits'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'price'      => ['type' => 'INT', 'default' => 0],
            'old_price'  => ['type' => 'INT', 'null' => true],
            'badge'      => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'currency'   => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'usd'],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort'       => ['type' => 'TINYINT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('billing_packages');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_packages');
    }
}
