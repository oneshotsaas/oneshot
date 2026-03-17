<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingPromotionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 64],
            'description'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'discount_type'  => ['type' => 'VARCHAR', 'constraint' => 20],
            'discount_value' => ['type' => 'INT', 'default' => 0],
            'applies_to'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'all'],
            'max_uses'       => ['type' => 'INT', 'null' => true],
            'used_count'     => ['type' => 'INT', 'default' => 0],
            'valid_from'     => ['type' => 'DATETIME', 'null' => true],
            'valid_until'    => ['type' => 'DATETIME', 'null' => true],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('billing_promotions');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_promotions');
    }
}
