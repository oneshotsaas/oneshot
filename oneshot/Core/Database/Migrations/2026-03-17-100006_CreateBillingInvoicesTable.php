<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingInvoicesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'unsigned' => true],
            'subscription_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'package_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'promotion_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'amount'          => ['type' => 'INT', 'default' => 0],
            'amount_discount' => ['type' => 'INT', 'default' => 0],
            'currency'        => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'usd'],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'description'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'paid_at'         => ['type' => 'DATETIME', 'null' => true],
            'tax_amount'      => ['type' => 'INT', 'default' => 0],
            'tax_rate'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '0.00'],
            'tax_source'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'manual'],
            'retry_count'     => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'next_retry_at'   => ['type' => 'DATETIME', 'null' => true],
            'data'            => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('subscription_id');
        $this->forge->createTable('billing_invoices');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_invoices');
    }
}
