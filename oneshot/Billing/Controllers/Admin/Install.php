<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Settings\Models\Setting;
use OneShot\Billing\Models\{Plan, PlanPrice, Package};

class Install extends Billing
{
    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->appendBC(__('billing.install', 'Setup'), route_to('admin.billing.install'));
    }

    public function index(): string
    {
        return $this->render('Billing::admin/install/index', [
            'status' => $this->buildStatus(),
        ]);
    }

    public function seedDemo(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->createDemoPlans();
        $this->createDemoPackages();
        return $this->redirectWith(route_to('admin.billing.install'), __('billing.install_demo_done', 'Demo data created'));
    }

    public function saveSettings(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post    = $this->request->getPost();
        $setting = new Setting();

        foreach ($this->settingsMeta() as $key => $meta) {
            $field = str_replace('.', '_', $key);
            if (isset($post[$field])) {
                $setting->store($key, $post[$field]);
            }
            $this->syncMeta($setting, $key, $meta);
        }

        // Auto-generate webhook URL tokens if empty, then sync meta from settingsMeta()
        $meta = $this->settingsMeta();
        foreach (['stripe', 'coinbase'] as $provider) {
            $tokenKey = 'billing.webhook_secret_' . $provider;
            if (empty(option($tokenKey))) {
                $setting->store($tokenKey, bin2hex(random_bytes(16)));
            }
            $this->syncMeta($setting, $tokenKey, $meta[$tokenKey]);
        }

        return $this->redirectWith(route_to('admin.billing.install'), __('billing.settings_saved', 'Settings saved'));
    }

    private function settingsMeta(): array
    {
        return [
            'billing.overdraft_mode' => [
                'type'    => 'select',
                'options' => json_encode([
                    ['value' => 'deny',  'label' => 'deny — block when balance is insufficient'],
                    ['value' => 'once',  'label' => 'once — allow going negative once per user'],
                    ['value' => 'limit', 'label' => 'limit — allow down to overdraft limit floor'],
                ]),
                'sort' => 1,
            ],
            'billing.overdraft_limit'          => ['type' => 'text',     'sort' => 2],
            'billing.stripe_secret_key'        => ['type' => 'password', 'sort' => 10],
            'billing.stripe_publishable_key'   => ['type' => 'text',     'sort' => 11],
            'billing.stripe_webhook_secret'    => ['type' => 'password', 'sort' => 12],
            'billing.coinbase_api_key'         => ['type' => 'password', 'sort' => 20],
            'billing.coinbase_webhook_secret'  => ['type' => 'password', 'sort' => 21],
            'billing.webhook_secret_stripe'    => ['type' => 'password', 'sort' => 13],
            'billing.webhook_secret_coinbase'  => ['type' => 'password', 'sort' => 22],
        ];
    }

    private function syncMeta(Setting $setting, string $key, array $meta): void
    {
        $row = $setting->where('key', $key)->where('user_id', null)->limit(1)->first();
        if (! $row) {
            return;
        }
        $setting->update($row->id, array_filter([
            'type'    => $meta['type'] ?? null,
            'options' => $meta['options'] ?? null,
            'sort'    => $meta['sort'] ?? null,
        ], fn($v) => $v !== null));
    }

    public function regenerateToken(): \CodeIgniter\HTTP\RedirectResponse
    {
        $provider = $this->request->getPost('provider');
        if (!in_array($provider, ['stripe', 'coinbase'], true)) {
            return $this->redirectWith(route_to('admin.billing.install'));
        }
        (new Setting())->store('billing.webhook_secret_' . $provider, bin2hex(random_bytes(16)));
        return $this->redirectWith(route_to('admin.billing.install'), __('billing.token_regenerated', 'Token regenerated'));
    }

    // ------------------------------------------------------------------

    private function buildStatus(): array
    {
        $planCount    = (new Plan())->where('deleted_at IS NULL')->countAllResults();
        $packageCount = (new Package())->where('deleted_at IS NULL')->countAllResults();

        $providers = [];
        foreach (['stripe', 'coinbase'] as $p) {
            $providers[$p] = [
                'api_key'        => !empty(option('billing.' . $p . (($p === 'stripe') ? '_secret_key' : '_api_key'))),
                'webhook_secret' => !empty(option('billing.' . $p . '_webhook_secret')),
                'url_token'      => option('billing.webhook_secret_' . $p, ''),
            ];
        }

        return [
            'overdraft_mode'  => option('billing.overdraft_mode', 'deny'),
            'overdraft_limit' => option('billing.overdraft_limit', '-50'),
            'stripe'          => $providers['stripe'],
            'coinbase'        => $providers['coinbase'],
            'plan_count'      => $planCount,
            'package_count'   => $packageCount,
        ];
    }

    private function createDemoPlans(): void
    {
        $planModel  = new Plan();
        $priceModel = new PlanPrice();

        $demos = [
            [
                'plan' => [
                    'name'             => 'Starter',
                    'slug'             => 'starter',
                    'description'      => 'Perfect for individuals and small projects.',
                    'credits_included' => 500,
                    'trial_days'       => 7,
                    'features'         => json_encode(['500 credits/month', 'Basic support', 'API access']),
                    'badge'            => null,
                    'hide_price'       => 0,
                    'is_active'        => 1,
                    'sort'             => 1,
                ],
                'prices' => [
                    ['interval' => 'month', 'price' => 900,  'currency' => 'usd'],
                    ['interval' => 'year',  'price' => 8640, 'currency' => 'usd', 'discount_pct' => 20],
                ],
            ],
            [
                'plan' => [
                    'name'             => 'Pro',
                    'slug'             => 'pro',
                    'description'      => 'For growing teams that need more power.',
                    'credits_included' => 2000,
                    'trial_days'       => 7,
                    'features'         => json_encode(['2000 credits/month', 'Priority support', 'API access', 'Usage analytics']),
                    'badge'            => 'Popular',
                    'hide_price'       => 0,
                    'is_active'        => 1,
                    'sort'             => 2,
                ],
                'prices' => [
                    ['interval' => 'month', 'price' => 2900,  'currency' => 'usd'],
                    ['interval' => 'year',  'price' => 27840, 'currency' => 'usd', 'discount_pct' => 20],
                ],
            ],
            [
                'plan' => [
                    'name'             => 'Business',
                    'slug'             => 'business',
                    'description'      => 'Unlimited power for large-scale production.',
                    'credits_included' => 10000,
                    'trial_days'       => 0,
                    'features'         => json_encode(['10 000 credits/month', 'Dedicated support', 'API access', 'Usage analytics', 'Custom integrations']),
                    'badge'            => 'Best Value',
                    'hide_price'       => 0,
                    'is_active'        => 1,
                    'sort'             => 3,
                ],
                'prices' => [
                    ['interval' => 'month', 'price' => 9900,  'currency' => 'usd'],
                    ['interval' => 'year',  'price' => 95040, 'currency' => 'usd', 'discount_pct' => 20],
                ],
            ],
        ];

        foreach ($demos as $item) {
            // Skip if slug already exists
            if ($planModel->where('slug', $item['plan']['slug'])->countAllResults() > 0) {
                continue;
            }
            $planModel->insert($item['plan']);
            $planId = $planModel->getInsertID();
            foreach ($item['prices'] as $price) {
                $priceModel->insert(['plan_id' => $planId] + $price);
            }
        }
    }

    private function createDemoPackages(): void
    {
        $model = new Package();

        $demos = [
            ['name' => '100 Credits',   'credits' => 100,  'price' => 199,  'currency' => 'usd', 'is_active' => 1, 'sort' => 1],
            ['name' => '500 Credits',   'credits' => 500,  'price' => 799,  'currency' => 'usd', 'is_active' => 1, 'sort' => 2, 'badge' => 'Popular'],
            ['name' => '2000 Credits',  'credits' => 2000, 'price' => 2499, 'currency' => 'usd', 'is_active' => 1, 'sort' => 3, 'badge' => 'Best Deal'],
        ];

        foreach ($demos as $pkg) {
            if ($model->where('name', $pkg['name'])->countAllResults() > 0) {
                continue;
            }
            $model->insert($pkg);
        }
    }
}
