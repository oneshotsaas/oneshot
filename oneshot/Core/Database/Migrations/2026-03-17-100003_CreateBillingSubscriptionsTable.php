<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingSubscriptionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'               => ['type' => 'INT', 'unsigned' => true],
            'plan_id'               => ['type' => 'INT', 'unsigned' => true],
            'plan_price_id'         => ['type' => 'INT', 'unsigned' => true],
            'promotion_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status'                => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'trial_ends_at'         => ['type' => 'DATETIME', 'null' => true],
            'current_period_start'  => ['type' => 'DATETIME', 'null' => true],
            'current_period_end'    => ['type' => 'DATETIME', 'null' => true],
            'canceled_at'           => ['type' => 'DATETIME', 'null' => true],
            'credits_balance'       => ['type' => 'DECIMAL', 'constraint' => '14,4', 'default' => '0.0000'],
            'overdraft_used'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'features_snapshot'     => ['type' => 'TEXT', 'null' => true],
            'is_active'             => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id', 'is_active']);
        $this->forge->addKey('user_id');
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->createTable('billing_subscriptions');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_subscriptions');
    }
}
