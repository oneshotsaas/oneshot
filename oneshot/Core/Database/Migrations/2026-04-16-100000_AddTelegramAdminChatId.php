<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTelegramAdminChatId extends Migration
{
    public function up(): void
    {
        $this->db->table('settings')->insertBatch([
            ['key' => 'telegram.admin_chat_ids', 'type' => 'text', 'value' => '', 'label' => '', 'sort' => 2, 'options' => null],
        ]);
    }

    public function down(): void
    {
        $this->db->table('settings')
            ->where('key', 'telegram.admin_chat_ids')
            ->delete();
    }
}
