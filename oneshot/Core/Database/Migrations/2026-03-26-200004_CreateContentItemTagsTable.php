<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentItemTagsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'content_item_id' => ['type' => 'INT', 'unsigned' => true],
            'content_tag_id'  => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey(['content_item_id', 'content_tag_id']);
        $this->forge->addKey('content_tag_id');
        $this->forge->createTable('content_item_tags');
    }

    public function down(): void
    {
        $this->forge->dropTable('content_item_tags');
    }
}
