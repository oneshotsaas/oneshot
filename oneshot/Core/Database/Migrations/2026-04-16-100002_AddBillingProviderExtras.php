<?php

namespace OneShot\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBillingProviderExtras extends Migration
{
    public function up(): void
    {
        // billing_subscriptions
        $this->forge->addColumn('billing_subscriptions', [
            'cancel_at_period_end' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'is_active',
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'cancel_at_period_end',
            ],
            'last_upgrade_invoice_id' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
                'after'   => 'provider',
            ],
        ]);

        // billing_promotions
        $this->forge->addColumn('billing_promotions', [
            'subscription_discount_duration' => [
                'type'       => 'ENUM',
                'constraint' => ['once', 'forever'],
                'null'       => false,
                'default'    => 'once',
                'after'      => 'discount_value',
            ],
        ]);

        // billing_plans
        $this->forge->addColumn('billing_plans', [
            'trial_credits' => [
                'type'    => 'INT UNSIGNED',
                'null'    => true,
                'default' => null,
                'after'   => 'trial_days',
            ],
        ]);

        // billing_plan_prices
        $this->forge->addColumn('billing_plan_prices', [
            'credits_grant' => [
                'type'       => 'ENUM',
                'constraint' => ['full', 'monthly'],
                'null'       => false,
                'default'    => 'full',
                'after'      => 'currency',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('billing_subscriptions', ['cancel_at_period_end', 'provider', 'last_upgrade_invoice_id']);
        $this->forge->dropColumn('billing_promotions', 'subscription_discount_duration');
        $this->forge->dropColumn('billing_plans', 'trial_credits');
        $this->forge->dropColumn('billing_plan_prices', 'credits_grant');
    }
}
