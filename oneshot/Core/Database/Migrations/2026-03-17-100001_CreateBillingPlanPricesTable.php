<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingPlanPricesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'plan_id'             => ['type' => 'INT', 'unsigned' => true],
            'interval'            => ['type' => 'VARCHAR', 'constraint' => 20],
            'price'               => ['type' => 'INT', 'default' => 0],
            'old_price'           => ['type' => 'INT', 'null' => true],
            'promo_price'         => ['type' => 'INT', 'null' => true],
            'promo_ends_at'       => ['type' => 'DATETIME', 'null' => true],
            'currency'            => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'usd'],
            'discount_pct'        => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'promo_discount_pct'  => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['plan_id', 'interval']);
        $this->forge->addKey('plan_id');
        $this->forge->createTable('billing_plan_prices');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_plan_prices');
    }
}
