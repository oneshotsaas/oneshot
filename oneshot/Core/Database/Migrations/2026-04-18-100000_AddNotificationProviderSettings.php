<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotificationProviderSettings extends Migration
{
    private array $keys = ['notifications.notify_provider', 'notifications.channels'];

    public function up(): void
    {
        $this->db->table('settings')->insertBatch([
            [
                'key'     => 'notifications.notify_provider',
                'type'    => 'select',
                'value'   => 'telegram',
                'label'   => '',
                'sort'    => 4,
                'options' => json_encode([
                    ['value' => 'telegram', 'label' => 'Telegram'],
                    ['value' => 'email',    'label' => 'Email'],
                ]),
            ],
            [
                'key'     => 'notifications.channels',
                'type'    => 'multiselect',
                'value'   => json_encode(['email', 'telegram']),
                'label'   => '',
                'sort'    => 5,
                'options' => json_encode([
                    ['value' => 'email',    'label' => 'Email'],
                    ['value' => 'telegram', 'label' => 'Telegram'],
                ]),
            ],
        ]);
    }

    public function down(): void
    {
        $this->db->table('settings')
            ->whereIn('key', $this->keys)
            ->delete();
    }
}
