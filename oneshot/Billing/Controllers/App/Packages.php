<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Billing\Models\Package;

class Packages extends Billing
{
    public function index(): string
    {
        $this->appendBC(__('billing.packages', 'Packages'), route_to('billing.packages'));

        return $this->render('Billing::app/packages/index', [
            'packages' => (new Package())->getActive(),
        ]);
    }

    public function buy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $packageId = signedId($hash);
        $userId    = session()->get('user_id');
        $promoCode = $this->request->getPost('promo_code') ?: null;

        $package = (new Package())->getById($packageId);
        if (!$package || !$package->is_active) {
            return $this->redirectWith(route_to('billing.packages'), __('billing.package_not_found', 'Package not found'), 'error');
        }

        $providers = array_filter(array_map('trim', explode(',', option('billing.payment_provider', 'stripe'))));

        // Fallback: no key + non-production → local purchase
        if (empty(option('billing.stripe_secret_key', '')) && ENVIRONMENT !== 'production') {
            try {
                $invoice = $this->billing->purchasePackage($userId, $packageId, $promoCode);
                \Config\Database::connect()->table('billing_invoices')->where('id', $invoice->id)->update(['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
                $this->billing->add($userId, (int)$package->credits, 'grant', ['subscription_id' => null, 'description' => 'Package: ' . $package->name, 'credit_source' => 'package']);
                event('billing.package_purchased', ['user_id' => $userId, 'package_id' => $packageId, 'credits' => $package->credits]);
                return $this->redirectWith(route_to('billing.packages'), __('billing.package_purchased', 'Package purchased'));
            } catch (\RuntimeException $e) {
                return $this->redirectWith(route_to('billing.packages'), $e->getMessage(), 'error');
            }
        }

        if (count($providers) > 1) {
            return redirect()->to(route_to('billing.packages.select_provider', $hash) . ($promoCode ? '?promo_code=' . urlencode($promoCode) : ''));
        }

        return $this->processPackageCheckout($hash, $providers[0] ?? 'stripe', $promoCode);
    }

    public function selectProvider(string $hash): string
    {
        $packageId = signedId($hash);
        $package   = (new Package())->getById($packageId);
        if (!$package) {
            return $this->redirectWith(route_to('billing.packages'), __('billing.not_found', 'Not found'), 'error');
        }

        $providers = array_filter(array_map('trim', explode(',', option('billing.payment_provider', 'stripe'))));
        $promoCode = $this->request->getGet('promo_code') ?? '';

        $this->appendBC(__('billing.packages', 'Packages'), route_to('billing.packages'));
        $this->appendBC(__('billing.select_payment_provider', 'Select Payment Method'));

        return $this->render('Billing::app/packages/select_provider', compact('package', 'hash', 'providers', 'promoCode'));
    }

    public function buyWithProvider(string $hash, string $provider): \CodeIgniter\HTTP\RedirectResponse
    {
        $promoCode = $this->request->getPost('promo_code') ?: null;
        return $this->processPackageCheckout($hash, $provider, $promoCode);
    }

    private function processPackageCheckout(string $hash, string $provider, ?string $promoCode): \CodeIgniter\HTTP\RedirectResponse
    {
        $packageId = signedId($hash);
        $userId    = session()->get('user_id');

        $package = (new Package())->getById($packageId);
        if (!$package || !$package->is_active) {
            return $this->redirectWith(route_to('billing.packages'), __('billing.package_not_found', 'Package not found'), 'error');
        }

        try {
            $p = service('oneTimePayment', $provider);

            // Calculate effective price (with promo discount)
            $effectivePrice = (int)$package->price;
            if ($promoCode) {
                $promo = (new \OneShot\Billing\Models\Promotion())->findByCode($promoCode);
                if ($promo && (new \OneShot\Billing\Models\Promotion())->isValid($promo, 'package')) {
                    $effectivePrice -= $this->billing->calcPromoDiscount($effectivePrice, $promo);
                    $effectivePrice  = max(0, $effectivePrice);
                }
            }

            // Get or create customer
            $customerId = $this->getOrCreateCustomer($userId, $p, $provider);

            $sessionParams = [
                'mode'        => 'payment',
                'customer'    => $customerId,
                'line_items'  => [['price_data' => [
                    'currency'     => strtolower($package->currency),
                    'unit_amount'  => $effectivePrice,
                    'product_data' => ['name' => $package->name],
                ], 'quantity' => 1]],
                'payment_intent_data' => ['setup_future_usage' => 'off_session'],
                'success_url' => site_url(route_to('billing.packages') . '?purchased=1&session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'  => site_url(route_to('billing.packages')),
                'metadata'    => [
                    'user_id'    => $userId,
                    'package_id' => $packageId,
                    'promo_code' => $promoCode ?? '',
                    'type'       => 'package',
                ],
            ];

            $session = $p->checkout($sessionParams);
            return redirect()->to($session['url']);

        } catch (\RuntimeException $e) {
            l(['user_id' => $userId, 'error' => $e->getMessage()], 'billing_errors');
            return $this->redirectWith(route_to('billing.packages'), $e->getMessage(), 'error');
        }
    }

    private function getOrCreateCustomer(int $userId, \OneShot\Core\Contracts\Payment $provider, string $providerName): string
    {
        $ref = $this->billing->getProviderRef('customer', $userId, $providerName);
        if ($ref) {
            return $ref->ref_id;
        }

        $user   = (new \OneShot\Auth\Models\User())->getById($userId);
        $result = $provider->customer($user->email, $user->name ?? $user->email, ['user_id' => $userId]);
        $this->billing->setProviderRef('customer', $userId, $providerName, $result['id']);
        return $result['id'];
    }
}
