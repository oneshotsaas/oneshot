<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingTransactionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'             => ['type' => 'INT', 'unsigned' => true],
            'subscription_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'amount'              => ['type' => 'DECIMAL', 'constraint' => '14,4'],
            'type'                => ['type' => 'VARCHAR', 'constraint' => 30],
            'action'              => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'unit_type'           => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'quantity'            => ['type' => 'INT', 'null' => true],
            'data'                => ['type' => 'TEXT', 'null' => true],
            'description'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'ref_type'            => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'ref_id'              => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'balance_after'       => ['type' => 'DECIMAL', 'constraint' => '14,4', 'default' => '0.0000'],
            'idempotency_key'     => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'ref_transaction_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'credit_source'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'actor_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'actor_type'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id', 'idempotency_key']);
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->createTable('billing_transactions');

        // Composite index for balance lookup (user_id + id DESC)
        $this->db->query('ALTER TABLE billing_transactions ADD INDEX idx_user_id_desc (user_id, id DESC)');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_transactions');
    }
}
