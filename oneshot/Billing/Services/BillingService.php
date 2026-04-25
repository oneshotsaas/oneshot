<?php

namespace OneShot\Billing\Services;

use OneShot\Billing\Models\{Plan, PlanPrice, Promotion, Subscription, Package, Transaction, Invoice, Cost, ProviderRef, WebhookEvent};

class BillingService
{
    private static array $featureCache = [];

    private Plan         $plans;
    private PlanPrice    $planPrices;
    private Promotion    $promotions;
    private Subscription $subscriptions;
    private Package      $packages;
    private Transaction  $transactions;
    private Invoice      $invoices;
    private Cost         $costs;
    private ProviderRef  $providerRefs;
    private WebhookEvent $webhookEvents;

    public function __construct()
    {
        $this->plans         = new Plan();
        $this->planPrices    = new PlanPrice();
        $this->promotions    = new Promotion();
        $this->subscriptions = new Subscription();
        $this->packages      = new Package();
        $this->transactions  = new Transaction();
        $this->invoices      = new Invoice();
        $this->costs         = new Cost();
        $this->providerRefs  = new ProviderRef();
        $this->webhookEvents = new WebhookEvent();
    }

    // -------------------------------------------------------------------------
    // Credit query
    // -------------------------------------------------------------------------

    public function getBalance(int $userId): float
    {
        // Authoritative source: last transaction's balance_after snapshot.
        // credits_balance on subscription is a write-through cache only.
        return (float) $this->transactions->getLastBalance($userId);
    }

    public function canAfford(int $userId, float $credits): bool
    {
        // Advisory only — not a gate. Real gate is inside deduct().
        $balance = $this->getBalance($userId);
        if ($balance >= $credits) {
            return true;
        }
        $mode = option('billing.overdraft_mode', 'deny');
        if ($mode === 'deny') {
            return false;
        }
        if ($mode === 'once') {
            $sub = $this->subscriptions->getActive($userId);
            return $sub && !$sub->overdraft_used;
        }
        if ($mode === 'limit') {
            $limit = (float) option('billing.overdraft_limit', 0);
            return ($balance - $credits) >= $limit;
        }
        return false;
    }

    public function hasFeature(int $userId, string $feature): bool
    {
        $cacheKey = "{$userId}:{$feature}";
        if (isset(self::$featureCache[$cacheKey])) {
            return self::$featureCache[$cacheKey];
        }

        $sub = $this->subscriptions->getActive($userId);
        if (!$sub) {
            self::$featureCache[$cacheKey] = false;
            return false;
        }

        $features = json_decode($sub->features_snapshot ?? '[]', true) ?: [];
        $result = in_array($feature, $features);
        self::$featureCache[$cacheKey] = $result;
        return $result;
    }

    // -------------------------------------------------------------------------
    // Credit operations
    // -------------------------------------------------------------------------

    public function add(int $userId, int|float $credits, string $type, array $ctx = []): void
    {
        $db  = \Config\Database::connect();
        $db->transStart();

        $sub = $db->query(
            'SELECT * FROM billing_subscriptions WHERE user_id = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1 FOR UPDATE',
            [$userId]
        )->getRowObject();

        $lastBalance = (float) $this->transactions->getLastBalance($userId);
        $newBalance  = $lastBalance + (float) $credits;

        $txData = [
            'user_id'         => $userId,
            'subscription_id' => $ctx['subscription_id'] ?? ($sub->id ?? null),
            'amount'          => (float) $credits,
            'type'            => $type,
            'action'          => $ctx['action'] ?? null,
            'description'     => $ctx['description'] ?? null,
            'ref_type'        => $ctx['ref_type'] ?? null,
            'ref_id'          => $ctx['ref_id'] ?? null,
            'balance_after'   => $newBalance,
            'credit_source'   => $ctx['credit_source'] ?? null,
            'actor_id'        => $ctx['actor_id'] ?? null,
            'actor_type'      => $ctx['actor_type'] ?? null,
            'data'            => isset($ctx['data']) ? json_encode($ctx['data']) : null,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $db->table('billing_transactions')->insert($txData);

        if ($sub) {
            $db->table('billing_subscriptions')
               ->where('user_id', $userId)
               ->update(['credits_balance' => $newBalance]);
        }

        $db->transComplete();
    }

    public function deduct(int $userId, float $credits, array $ctx = []): bool
    {
        // Normalize idempotency_key
        $ikey = ($ctx['idempotency_key'] ?? null) ?: null;
        $force = $ctx['force'] ?? false;

        $db = \Config\Database::connect();
        $db->transStart();

        // Step 1: Lock subscription row
        $sub = $db->query(
            'SELECT * FROM billing_subscriptions WHERE user_id = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1 FOR UPDATE',
            [$userId]
        )->getRowObject();

        if (!$sub) {
            $db->transRollback();
            return false;
        }

        // Step 2: Get authoritative balance from last transaction
        $lastBalance = $this->transactions->getLastBalance($userId);
        $cachedBalance = (float) $sub->credits_balance;

        // Drift detection
        if (abs($lastBalance - $cachedBalance) > 0.0001) {
            l(['user_id' => $userId, 'last_balance' => $lastBalance, 'cached' => $cachedBalance, 'diff' => abs($lastBalance - $cachedBalance)], 'billing_drift');
        }

        // Step 3: PHP-level balance check
        if (!$force) {
            $allowed = $this->checkOverdraft($userId, $lastBalance, $credits, $sub);
            if (!$allowed) {
                $db->transRollback();
                return false;
            }
        }

        $newBalance  = $lastBalance - $credits;
        $useOverdraft = ($lastBalance < $credits) ? 1 : 0;

        // Step 4: INSERT transaction (with idempotency)
        $txData = [
            'user_id'            => $userId,
            'subscription_id'    => $sub->id,
            'amount'             => -$credits,
            'type'               => $ctx['type'] ?? 'deduction',
            'action'             => $ctx['action'] ?? null,
            'unit_type'          => $ctx['unit_type'] ?? null,
            'quantity'           => $ctx['quantity'] ?? null,
            'description'        => $ctx['description'] ?? null,
            'ref_type'           => $ctx['ref_type'] ?? null,
            'ref_id'             => $ctx['ref_id'] ?? null,
            'balance_after'      => $newBalance,
            'idempotency_key'    => $ikey,
            'ref_transaction_id' => $ctx['ref_transaction_id'] ?? null,
            'credit_source'      => $ctx['credit_source'] ?? null,
            'actor_id'           => $ctx['actor_id'] ?? null,
            'actor_type'         => $ctx['actor_type'] ?? null,
            'data'               => isset($ctx['data']) ? json_encode($ctx['data']) : null,
            'created_at'         => date('Y-m-d H:i:s'),
        ];

        if ($ikey !== null) {
            // ON DUPLICATE KEY path — idempotency via UNIQUE(user_id, idempotency_key)
            $db->table('billing_transactions')
               ->set($txData)
               ->onConflict(['user_id', 'idempotency_key'], ['idempotency_key' => $ikey])
               ->insert();

            if ($db->affectedRows() === 0) {
                $db->transRollback();
                return true; // Already processed
            }
        } else {
            $db->table('billing_transactions')->insert($txData);
        }

        // Step 5: Update subscription cache
        $updateData = ['credits_balance' => $newBalance];
        if ($useOverdraft) {
            $updateData['overdraft_used'] = 1;
        }
        $db->table('billing_subscriptions')
           ->where('user_id', $userId)
           ->update($updateData);

        $db->transComplete();
        return true;
    }

    private function checkOverdraft(int $userId, float $balance, float $amount, object $sub): bool
    {
        if ($balance >= $amount) {
            return true;
        }

        $mode = option('billing.overdraft_mode', 'deny');

        if ($mode === 'deny') {
            return false;
        }

        if ($mode === 'once') {
            return !$sub->overdraft_used;
        }

        if ($mode === 'limit') {
            $limit = (float) option('billing.overdraft_limit', 0);
            return ($balance - $amount) >= $limit;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Cost calculation
    // -------------------------------------------------------------------------

    public function calculateCost(string $action, array $params = []): float
    {
        $cost = $this->costs->findByAction($action);
        if (!$cost) {
            return 0.0;
        }

        $meta = $cost->meta ? json_decode($cost->meta, true) : [];
        $cpu  = (float) $cost->cost_per_unit;

        switch ($cost->unit_type) {
            case 'unit':
                $paramKey = $meta['param'] ?? 'units';
                $qty      = (float) ($params[$paramKey] ?? 1);
                $variant  = $meta['variants'][$params[$paramKey] ?? ''] ?? null;
                if ($variant && $variant['type'] === 'fixed') {
                    return (float) $variant['value'];
                }
                $mult = ($variant && $variant['type'] === 'multiply') ? (float) $variant['value'] : 1.0;
                return (float) ($cpu * $qty * $mult);

            case 'second':
                $qty  = (float) ($params[$meta['param'] ?? 'seconds'] ?? 0);
                $mult = 1.0;
                foreach ($meta['variants'] ?? [] as $key => $variant) {
                    if ($params[$key] ?? false) {
                        if ($variant['type'] === 'fixed') {
                            return (float) $variant['value'];
                        }
                        if ($variant['type'] === 'multiply') {
                            $mult *= (float) $variant['value'];
                        }
                    }
                }
                return (float) ($cpu * $qty * $mult);

            case 'token':
                $inputKey  = $meta['input_key']  ?? 'input_tokens';
                $outputKey = $meta['output_key'] ?? 'output_tokens';
                $cachedKey = $meta['cached_key'] ?? 'cached_tokens';
                $input     = (float) ($params[$inputKey]  ?? $params['tokens'] ?? 0);
                $output    = (float) ($params[$outputKey] ?? 0);
                $cached    = (float) ($params[$cachedKey] ?? 0);
                $outMult   = (float) ($meta['output_multiplier'] ?? 1);
                $cacheDis  = (float) ($meta['cached_discount']   ?? 0);
                $batch     = (float) ($meta['batch']             ?? 1000);
                $effective = max(0.0, $input + $output * $outMult - $cached * $cacheDis);
                return (float) (ceil($effective / $batch) * $cpu);
        }

        return 0.0;
    }

    public function charge(int $userId, string $action, array $params = [], string $idempotencyKey = ''): bool
    {
        // ❗ Use BEFORE the action.
        $cost = $this->calculateCost($action, $params);
        if ($cost === 0.0) {
            return true;
        }

        $costRow  = $this->costs->findByAction($action);
        $snapshot = $costRow ? [
            'cost_per_unit' => $costRow->cost_per_unit,
            'unit_type'     => $costRow->unit_type,
            'meta'          => $costRow->meta ? json_decode($costRow->meta, true) : [],
            'meta_version'  => $costRow->meta_version,
        ] : [];

        return $this->deduct($userId, $cost, [
            'action'          => $action,
            'unit_type'       => $costRow->unit_type ?? null,
            'idempotency_key' => $idempotencyKey ?: null,
            'data'            => array_merge($params, ['_cost_snapshot' => $snapshot]),
        ]);
    }

    public function chargeForce(int $userId, string $action, array $params = [], string $idempotencyKey = ''): bool
    {
        if (empty($idempotencyKey)) {
            throw new \InvalidArgumentException('chargeForce() requires a non-empty idempotency key');
        }

        $cost = $this->calculateCost($action, $params);
        if ($cost === 0.0) {
            return false;
        }

        $costRow  = $this->costs->findByAction($action);
        $snapshot = $costRow ? [
            'cost_per_unit' => $costRow->cost_per_unit,
            'unit_type'     => $costRow->unit_type,
            'meta'          => $costRow->meta ? json_decode($costRow->meta, true) : [],
            'meta_version'  => $costRow->meta_version,
        ] : [];

        return $this->deduct($userId, $cost, [
            'action'          => $action,
            'unit_type'       => $costRow->unit_type ?? null,
            'idempotency_key' => $idempotencyKey,
            'force'           => true,
            'data'            => array_merge($params, ['_cost_snapshot' => $snapshot]),
        ]);
    }

    public function estimate(int $userId, string $action, array $params = []): array
    {
        $cost    = $this->calculateCost($action, $params);
        $balance = $this->getBalance($userId);
        $canAfford = $this->canAfford($userId, $cost);

        $wouldUseOverdraft = false;
        if ($balance < $cost) {
            $mode = option('billing.overdraft_mode', 'deny');
            if ($mode !== 'deny') {
                $wouldUseOverdraft = $canAfford;
            }
        }

        return [
            'cost'               => $cost,
            'can_afford'         => $canAfford,
            'balance'            => $balance,
            'would_use_overdraft'=> $wouldUseOverdraft,
        ];
    }

    public function getCost(string $action): ?object
    {
        return $this->costs->findByAction($action);
    }

    public function rebuildBalance(int $userId): float
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $sub = $db->query(
            'SELECT * FROM billing_subscriptions WHERE user_id = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1 FOR UPDATE',
            [$userId]
        )->getRowObject();

        $sum = $this->transactions->sumForUser($userId);

        if ($sub) {
            $db->table('billing_subscriptions')
               ->where('user_id', $userId)
               ->update(['credits_balance' => $sum]);
        }

        $db->transComplete();
        return $sum;
    }

    public function auditBalance(int $userId = 0): array
    {
        $drifts = [];
        $db     = \Config\Database::connect();

        $query = $db->table('billing_subscriptions')
                    ->select('user_id, credits_balance')
                    ->where('is_active', 1)
                    ->where('deleted_at IS NULL');

        if ($userId > 0) {
            $query->where('user_id', $userId);
        }

        foreach ($query->get()->getResultObject() as $sub) {
            $uid         = (int) $sub->user_id;
            $sum         = $this->transactions->sumForUser($uid);
            $lastBalance = $this->transactions->getLastBalance($uid);
            $cached      = (float) $sub->credits_balance;
            $diff        = abs($sum - $lastBalance);

            if ($diff > 0.0001) {
                $entry = ['user_id' => $uid, 'sum' => $sum, 'last_balance_after' => $lastBalance, 'cached' => $cached, 'diff' => $diff];
                l($entry, 'billing_drift');
                $drifts[] = $entry;
            }
        }

        return $drifts;
    }

    // -------------------------------------------------------------------------
    // Subscription
    // -------------------------------------------------------------------------

    public function subscribe(int $userId, int $planId, string $interval, ?string $promoCode = null): ?object
    {
        $db = \Config\Database::connect();

        $planPrice = $this->planPrices->getByInterval($planId, $interval);
        if (!$planPrice) {
            throw new \RuntimeException('Plan price not found');
        }

        $plan = $this->plans->getById($planId);
        if (!$plan || !$plan->is_active) {
            throw new \RuntimeException('Plan not found');
        }

        $db->transStart();

        // Cancel existing subscription if switching plans
        $existing = $db->query(
            'SELECT * FROM billing_subscriptions WHERE user_id = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1 FOR UPDATE',
            [$userId]
        )->getRowObject();

        if ($existing) {
            if ((int)$existing->plan_id === $planId && $existing->plan_price_id === $planPrice->id) {
                $db->transRollback();
                throw new \RuntimeException(__('billing.already_subscribed', 'You are already on this plan'));
            }
            $db->table('billing_subscriptions')->where('id', $existing->id)->update([
                'status'      => 'canceled',
                'is_active'   => null,
                'canceled_at' => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        // Promo handling
        $promoId       = null;
        $amountDiscount = 0;
        $effectivePrice = $this->getEffectivePrice($planPrice);

        if ($promoCode) {
            $promo = $this->promotions->findByCode($promoCode);
            if (!$promo || !$this->promotions->isValid($promo, 'subscription')) {
                $db->transRollback();
                throw new \RuntimeException(__('billing.promo_invalid', 'Invalid promo code'));
            }

            // Atomic increment
            $affected = $db->table('billing_promotions')
                           ->where('id', $promo->id)
                           ->where('is_active', 1)
                           ->where('(max_uses IS NULL OR used_count < max_uses)')
                           ->set('used_count', 'used_count + 1', false)->update();

            if ($db->affectedRows() === 0) {
                $db->transRollback();
                throw new \RuntimeException(__('billing.promo_exhausted', 'Promo code is no longer available'));
            }

            $promoId        = $promo->id;
            $amountDiscount = $this->calcPromoDiscount($effectivePrice, $promo);
        }

        // Determine trial
        $now    = date('Y-m-d H:i:s');
        $status = 'active';
        $trialEndsAt = null;

        if ($plan->trial_days > 0) {
            $status      = 'trial';
            $trialEndsAt = date('Y-m-d H:i:s', strtotime("+{$plan->trial_days} days"));
        }

        $periodEnd = $this->calcPeriodEnd($interval);

        // INSERT subscription — UNIQUE(user_id, is_active) prevents doubles
        $subData = [
            'user_id'              => $userId,
            'plan_id'              => $planId,
            'plan_price_id'        => $planPrice->id,
            'promotion_id'         => $promoId,
            'status'               => $status,
            'trial_ends_at'        => $trialEndsAt,
            'current_period_start' => $now,
            'current_period_end'   => $periodEnd,
            'credits_balance'      => 0,
            'is_active'            => 1,
            'features_snapshot'    => $plan->features,
        ];

        try {
            $db->table('billing_subscriptions')->insert($subData);
            $subId = $db->insertID();
        } catch (\Exception $e) {
            $db->transRollback();
            throw new \RuntimeException(__('billing.already_subscribed', 'Already subscribed'));
        }

        // INSERT invoice
        $db->table('billing_invoices')->insert([
            'user_id'         => $userId,
            'subscription_id' => $subId,
            'promotion_id'    => $promoId,
            'amount'          => $effectivePrice,
            'amount_discount' => $amountDiscount,
            'currency'        => $planPrice->currency,
            'status'          => 'open',
            'description'     => $plan->name . ' — ' . $interval,
            'data'            => json_encode([
                'plan_name'   => $plan->name,
                'plan_price'  => $effectivePrice,
                'interval'    => $interval,
            ]),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        // Grant initial credits (no inner transaction — we're already inside one)
        $credits = $this->creditsForInterval((float) $plan->credits_included, $interval);

        if ($credits > 0) {
            $lastBalance = (float) $this->transactions->getLastBalance($userId);
            $newBalance  = $lastBalance + $credits;

            $db->table('billing_transactions')->insert([
                'user_id'         => $userId,
                'subscription_id' => $subId,
                'amount'          => $credits,
                'type'            => 'grant',
                'description'     => __('billing.credits_granted', 'Credits granted'),
                'balance_after'   => $newBalance,
                'credit_source'   => 'subscription',
                'created_at'      => $now,
            ]);

            $db->table('billing_subscriptions')
               ->where('id', $subId)
               ->update(['credits_balance' => $newBalance]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Subscribe transaction failed');
        }

        return $this->subscriptions->getById($subId);
    }

    public function upgradeCredits(int $subId, int $newPlanId, int $remainingDays, int $totalDays, ?int $invoiceId = null): void
    {
        if ($remainingDays <= 0) {
            return;
        }

        $db = \Config\Database::connect();

        // Idempotency: skip if this invoice was already applied
        if ($invoiceId !== null) {
            $sub = $this->subscriptions->getById($subId);
            if ($sub && (int)$sub->last_upgrade_invoice_id === $invoiceId) {
                return;
            }
        }

        $plan = $this->plans->getById($newPlanId);
        if (!$plan) {
            return;
        }

        $ratio   = $totalDays > 0 ? ($remainingDays / $totalDays) : 0;
        $credits = round((float)$plan->credits_included * $ratio);

        if ($credits <= 0) {
            return;
        }

        $sub = $this->subscriptions->getById($subId);
        if (!$sub) {
            return;
        }

        $this->add($sub->user_id, $credits, 'grant', [
            'subscription_id' => $subId,
            'description'     => __('billing.credits_upgraded', 'Upgrade credits'),
            'credit_source'   => 'upgrade',
            'data'            => ['new_plan_id' => $newPlanId, 'remaining_days' => $remainingDays, 'total_days' => $totalDays],
        ]);

        if ($invoiceId !== null) {
            $db->table('billing_subscriptions')
               ->where('id', $subId)
               ->update(['last_upgrade_invoice_id' => $invoiceId]);
        }
    }

    public function cancelSubscription(int $userId): bool
    {
        $sub = $this->subscriptions->getActive($userId);
        if (!$sub) {
            return false;
        }

        $this->subscriptions->save([
            'id'          => $sub->id,
            'status'      => 'canceled',
            'canceled_at' => date('Y-m-d H:i:s'),
            'is_active'   => null,
        ]);

        return true;
    }

    public function getSubscription(int $userId): ?object
    {
        return $this->subscriptions->getActive($userId);
    }

    public function hasActiveSubscription(int $userId): bool
    {
        return $this->subscriptions->getActive($userId) !== null;
    }

    public function isInTrial(int $userId): bool
    {
        $sub = $this->subscriptions->getActive($userId);
        return $sub && $sub->status === 'trial';
    }

    public function renewCredits(int $subscriptionId, int $invoiceId): void
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Atomic idempotency guard
        $db->table('billing_invoices')
           ->where('id', $invoiceId)
           ->where('status !=', 'paid')
           ->update([
               'status'     => 'paid',
               'paid_at'    => date('Y-m-d H:i:s'),
               'updated_at' => date('Y-m-d H:i:s'),
           ]);

        if ($db->affectedRows() === 0) {
            $db->transRollback();
            return; // Already paid
        }

        $sub = $this->subscriptions->getById($subscriptionId);
        if (!$sub) {
            $db->transRollback();
            return;
        }

        $plan = $this->plans->getById($sub->plan_id);
        if (!$plan) {
            $db->transRollback();
            return;
        }

        $planPrice = $this->planPrices->getById($sub->plan_price_id);
        $periodEnd = $planPrice ? $this->calcPeriodEnd($planPrice->interval) : null;

        $db->table('billing_subscriptions')->where('id', $subscriptionId)->update([
            'status'               => 'active',
            'current_period_start' => date('Y-m-d H:i:s'),
            'current_period_end'   => $periodEnd,
            'credits_balance'      => 0,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        // Grant credits (outside main transaction to avoid nesting issues)
        $credits = $this->creditsForInterval((float) $plan->credits_included, $planPrice->interval ?? 'month');
        if ($credits > 0) {
            $this->add($sub->user_id, $credits, 'grant', [
                'subscription_id' => $subscriptionId,
                'description'     => __('billing.credits_renewed', 'Credits renewed'),
                'credit_source'   => 'subscription',
            ]);
        }
    }

    public function getEffectivePrice(object $planPrice): int
    {
        if ($planPrice->promo_price !== null && $planPrice->promo_ends_at !== null) {
            if ($planPrice->promo_ends_at > date('Y-m-d H:i:s')) {
                return (int) $planPrice->promo_price;
            }
        }
        return (int) $planPrice->price;
    }

    // -------------------------------------------------------------------------
    // Package purchase
    // -------------------------------------------------------------------------

    public function purchasePackage(int $userId, int $packageId, ?string $promoCode = null): object
    {
        $package = $this->packages->getById($packageId);
        if (!$package || !$package->is_active) {
            throw new \RuntimeException(__('billing.package_not_found', 'Package not found'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $price          = (int) $package->price;
        $amountDiscount = 0;
        $promoId        = null;

        if ($promoCode) {
            $promo = $this->promotions->findByCode($promoCode);
            if (!$promo || !$this->promotions->isValid($promo, 'package')) {
                $db->transRollback();
                throw new \RuntimeException(__('billing.promo_invalid', 'Invalid promo code'));
            }

            $db->table('billing_promotions')
               ->where('id', $promo->id)
               ->where('is_active', 1)
               ->where('(max_uses IS NULL OR used_count < max_uses)')
               ->set('used_count', 'used_count + 1', false)->update();

            if ($db->affectedRows() === 0) {
                $db->transRollback();
                throw new \RuntimeException(__('billing.promo_exhausted', 'Promo code exhausted'));
            }

            $promoId        = $promo->id;
            $amountDiscount = $this->calcPromoDiscount($price, $promo);
        }

        $now = date('Y-m-d H:i:s');
        $db->table('billing_invoices')->insert([
            'user_id'         => $userId,
            'package_id'      => $packageId,
            'promotion_id'    => $promoId,
            'amount'          => $price,
            'amount_discount' => $amountDiscount,
            'currency'        => $package->currency,
            'status'          => 'open',
            'description'     => $package->name,
            'data'            => json_encode([
                'package_credits' => $package->credits,
                'package_name'    => $package->name,
                'plan_price'      => $price,
            ]),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
        $invoiceId = $db->insertID();

        $db->transComplete();

        return $this->invoices->getById($invoiceId);
    }

    // -------------------------------------------------------------------------
    // Provider ref helpers
    // -------------------------------------------------------------------------

    public function setProviderRef(string $entityType, int $entityId, string $provider, string $refId, array $meta = []): void
    {
        $existing = $this->providerRefs->findRef($entityType, $entityId, $provider);
        $data = [
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'provider'    => $provider,
            'ref_id'      => $refId,
            'meta'        => $meta ? json_encode($meta) : null,
        ];

        if ($existing) {
            $this->providerRefs->save(array_merge($data, ['id' => $existing->id]));
        } else {
            $this->providerRefs->add($data);
        }
    }

    public function getProviderRef(string $entityType, int $entityId, string $provider): ?object
    {
        return $this->providerRefs->findRef($entityType, $entityId, $provider);
    }

    public function getProviderRefs(string $entityType, int $entityId): array
    {
        return $this->providerRefs->findAllForEntity($entityType, $entityId);
    }

    // -------------------------------------------------------------------------
    // Webhook event deduplication
    // -------------------------------------------------------------------------

    /**
     * Attempt to claim ownership of a webhook event.
     * Returns false if another worker owns it or it's already processed.
     * Caller MUST call markWebhookProcessed() after successful processing.
     */
    public function claimWebhookEvent(string $provider, string $eventId, string $eventType, string $rawPayload): string|false
    {
        $token       = bin2hex(random_bytes(32));
        $incomingHash = hash('sha256', $rawPayload);
        $db           = \Config\Database::connect();

        // INSERT or update via raw query for full ON DUPLICATE KEY control
        $now = date('Y-m-d H:i:s');
        $db->query(
            "INSERT INTO billing_webhook_events
                (provider, event_id, event_type, status, processing_token, processing_started_at, payload_hash)
             VALUES (?, ?, ?, 'processing', ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               processing_token = CASE
                 WHEN status = 'processed' THEN processing_token
                 WHEN status = 'processing' AND processing_started_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN processing_token
                 ELSE ?
               END,
               status = CASE
                 WHEN status = 'processed' THEN 'processed'
                 WHEN status = 'processing' AND processing_started_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN 'processing'
                 ELSE 'processing'
               END,
               processing_started_at = IF(status = 'failed' OR processing_started_at <= DATE_SUB(NOW(), INTERVAL 10 MINUTE), NOW(), processing_started_at),
               payload_hash = IF(payload_hash IS NULL, ?, payload_hash)",
            [$provider, $eventId, $eventType, $token, $now, $incomingHash, $token, $incomingHash]
        );

        // Re-read with lock to check ownership
        $row = $db->query(
            'SELECT * FROM billing_webhook_events WHERE provider = ? AND event_id = ? LIMIT 1 FOR UPDATE',
            [$provider, $eventId]
        )->getRowObject();

        if (!$row) {
            return false;
        }

        // Payload mismatch warning
        if ($row->payload_hash && $row->payload_hash !== $incomingHash) {
            l(['event_id' => $eventId, 'provider' => $provider, 'warning' => 'payload_hash_mismatch'], 'billing_webhook');
        }

        if ($row->status === 'processed') {
            return false;
        }

        if ($row->processing_token !== $token) {
            return false; // Another worker owns it
        }

        return $token;
    }

    public function markWebhookProcessed(string $provider, string $eventId): void
    {
        \Config\Database::connect()->table('billing_webhook_events')
            ->where('provider', $provider)
            ->where('event_id', $eventId)
            ->update(['status' => 'processed', 'processed_at' => date('Y-m-d H:i:s')]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function calcPeriodEnd(string $interval): string
    {
        $map = [
            'month'    => '+1 month',
            'quarter'  => '+3 months',
            'halfyear' => '+6 months',
            'year'     => '+1 year',
        ];
        return date('Y-m-d H:i:s', strtotime($map[$interval] ?? '+1 month'));
    }

    private function creditsForInterval(float $creditsIncluded, string $interval): float
    {
        $multiplier = ['month' => 1, 'quarter' => 3, 'halfyear' => 6, 'year' => 12];
        return $creditsIncluded * ($multiplier[$interval] ?? 1);
    }

    public function calcPromoDiscount(int $price, object $promo): int
    {
        if ($promo->discount_type === 'percent') {
            return (int) round($price * $promo->discount_value / 100);
        }
        return min($price, (int) $promo->discount_value);
    }
}
