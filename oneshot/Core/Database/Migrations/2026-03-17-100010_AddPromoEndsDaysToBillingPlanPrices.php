<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPromoEndsDaysToBillingPlanPrices extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('billing_plan_prices', [
            'promo_ends_days' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'promo_ends_at',
                'comment'    => 'Days from user registration until promo ends (alternative to fixed promo_ends_at)',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('billing_plan_prices', 'promo_ends_days');
    }
}
