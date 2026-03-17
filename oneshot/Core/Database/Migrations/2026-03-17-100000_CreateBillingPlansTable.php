<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingPlansTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 128],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 64],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'credits_included' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'trial_days'       => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'features'         => ['type' => 'TEXT', 'null' => true],
            'badge'            => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'hide_price'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort'             => ['type' => 'TINYINT', 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('billing_plans');
    }

    public function down(): void
    {
        $this->forge->dropTable('billing_plans');
    }
}
