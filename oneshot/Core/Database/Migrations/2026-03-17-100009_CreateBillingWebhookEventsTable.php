<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingWebhookEventsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'provider'               => ['type' => 'VARCHAR', 'constraint' => 30],
            'event_id'               => ['type' => 'VARCHAR', 'constraint' => 128],
            'event_type'             => ['type' => 'VARCHAR', 'constraint' => 64],
            'status'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'processing'],
            'processing_token'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'processing_started_at'  => ['type' => 'DATETIME', 'null' => true],
            'processed_at'           => ['type' => 'DATETIME', 'null' => true],
            'payload_hash'           => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['provider', 'event_id']);
        $this->forge->createTable('billing_webhook_events');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_webhook_events');
    }
}
