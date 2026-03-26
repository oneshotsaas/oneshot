<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentTagsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 191],
            'image'            => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true, 'default' => null],
            'meta_title'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'meta_description' => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true, 'default' => null],
            'template'         => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'tag'],
            'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('is_active');
        $this->forge->createTable('content_tags');
    }

    public function down(): void
    {
        $this->forge->dropTable('content_tags');
    }
}
