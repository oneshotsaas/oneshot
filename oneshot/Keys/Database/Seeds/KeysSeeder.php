<?php

namespace OneShot\Keys\Database\Seeds;

/**
 * Keys seeder — inserts default settings rows for the Keys module.
 * Plain class (not CI4 Seeder) so it can be called from DatabaseSeeder.
 */
class KeysSeeder
{
    public function run(): void
    {
        $model = new \OneShot\Settings\Models\Setting();

        $rows = [
            [
                'key'   => 'keys.prefix',
                'value' => 'oneshot_',
                'type'  => 'text',
                'label' => '',
                'sort'  => 10,
            ],
        ];

        foreach ($rows as $row) {
            $existing = $model->where('key', $row['key'])->where('user_id', null)->limit(1)->first();
            if ($existing) {
                continue;
            }

            $model->store($row['key'], $row['value'], null);

            $justInserted = $model->where('key', $row['key'])->where('user_id', null)->limit(1)->first();
            if ($justInserted) {
                $model->update($justInserted->id, [
                    'type'  => $row['type'],
                    'label' => $row['label'],
                    'sort'  => $row['sort'],
                ]);
            }
        }
    }
}
