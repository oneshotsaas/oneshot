<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTelegramSupport extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('auth_users', [
            'telegram_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'timezone',
            ],
        ]);

        $this->db->table('settings')->insertBatch([
            ['key' => 'telegram.bot_token',        'type' => 'password', 'value' => '', 'label' => '', 'sort' => 1, 'options' => null],
            ['key' => 'notifications.defaults',     'type' => 'json',     'value' => '{}', 'label' => '', 'sort' => 2, 'options' => null],
            ['key' => 'notifications.queue_mode',   'type' => 'boolean',  'value' => '0', 'label' => '', 'sort' => 3, 'options' => null],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('auth_users', 'telegram_id');
        $this->db->table('settings')
            ->whereIn('key', ['telegram.bot_token', 'notifications.defaults', 'notifications.queue_mode'])
            ->delete();
    }
}
