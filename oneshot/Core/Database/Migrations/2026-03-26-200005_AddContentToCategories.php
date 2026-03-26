<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContentToCategories extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('content_categories', [
            'content' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'meta_description',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('content_categories', 'content');
    }
}
