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
        $promoCode = $this->request->getPost('promo_code');

        try {
            $this->billing->purchasePackage($userId, $packageId, $promoCode ?: null);
            return $this->redirectWith(route_to('billing.packages'), __('billing.package_purchased', 'Package purchased'));
        } catch (\RuntimeException $e) {
            return $this->redirectWith(route_to('billing.packages'), $e->getMessage(), 'error');
        }
    }
}
