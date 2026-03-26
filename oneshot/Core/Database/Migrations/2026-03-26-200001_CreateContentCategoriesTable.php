<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentCategoriesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'parent_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'title'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 191],
            'image'            => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true, 'default' => null],
            'meta_title'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'meta_description' => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true, 'default' => null],
            'template'         => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'category'],
            'sort'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['parent_id', 'slug']);
        $this->forge->addKey('is_active');
        $this->forge->createTable('content_categories');
    }

    public function down(): void
    {
        $this->forge->dropTable('content_categories');
    }
}
