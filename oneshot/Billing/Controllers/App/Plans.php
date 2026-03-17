<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Billing\Models\{Plan, PlanPrice};

class Plans extends Billing
{
    public function index(): string
    {
        $this->appendBC(__('billing.plans', 'Plans'), route_to('billing.plans'));

        $userId = (int) session()->get('user_id');
        $user   = (new \OneShot\Auth\Models\User())->getById($userId);

        $plans     = (new Plan())->getActive();
        $planIds   = array_column($plans, 'id');
        $prices    = [];
        $promoEnds = []; // [plan_id][interval] => unix timestamp or null

        if ($planIds) {
            $rawPrices = (new PlanPrice())->whereIn('plan_id', $planIds)->findAll();
            foreach ($rawPrices as $p) {
                $prices[$p->plan_id][$p->interval] = $p;

                // Resolve effective promo end timestamp for this user
                $promoEnds[$p->plan_id][$p->interval] = $this->resolvePromoEnd($p, $user);
            }
        }

        $sub             = $this->billing->getSubscription($userId);
        $currentPlanId   = $sub ? (int) $sub->plan_id : 0;
        $currentInterval = $sub ? ((new PlanPrice())->getById($sub->plan_price_id)->interval ?? '') : '';

        $planFeatures = [];
        foreach ($plans as $plan) {
            $planFeatures[$plan->id] = json_decode($plan->features ?? '[]', true) ?: [];
        }

        return $this->render('Billing::app/plans/index', compact(
            'plans', 'prices', 'currentPlanId', 'currentInterval', 'planFeatures', 'promoEnds'
        ));
    }

    /**
     * Returns UNIX timestamp when promo ends for this user, or null if no promo.
     * - promo_ends_at: fixed datetime
     * - promo_ends_days: X days after user registration
     */
    private function resolvePromoEnd(object $price, ?object $user): ?int
    {
        if (empty($price->promo_price)) {
            return null;
        }

        if (!empty($price->promo_ends_at)) {
            $ts = strtotime($price->promo_ends_at);
            return $ts > time() ? $ts : null;
        }

        if (!empty($price->promo_ends_days) && $user && !empty($user->created_at)) {
            $ts = strtotime($user->created_at) + ((int)$price->promo_ends_days * 86400);
            return $ts > time() ? $ts : null;
        }

        // promo_price set but no end — always active
        return PHP_INT_MAX;
    }
}
