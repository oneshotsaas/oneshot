<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function subscriptionPayment(string $provider): \OneShot\Core\Contracts\Payment
    {
        return static::resolvePaymentProvider($provider);
    }

    public static function oneTimePayment(string $provider): \OneShot\Core\Contracts\Payment
    {
        return static::resolvePaymentProvider($provider);
    }

    private static function resolvePaymentProvider(string $name): \OneShot\Core\Contracts\Payment
    {
        return match(strtolower($name)) {
            'stripe'   => new \Providers\Stripe\Stripe(option('billing.stripe_secret_key', '')),
            default    => throw new \RuntimeException("Unknown payment provider: {$name}"),
        };
    }


    public static function notify(string $channel = ''): \OneShot\Core\Contracts\Notify
    {
        if ($channel === '') {
            $channel = option('notifications.notify_provider', 'telegram');
        }

        return match (strtolower($channel)) {
            'email'    => new \Providers\Email\Email(),
            'telegram' => new \Providers\Telegram\Telegram(),
            default    => throw new \RuntimeException("Unknown notify channel: {$channel}"),
        };
    }

    public static function mailAuth(bool $getShared = true): \OneShot\Auth\Services\MailService
    {
        if ($getShared) {
            return static::getSharedInstance('mailAuth');
        }

        return new \OneShot\Auth\Services\MailService();
    }

    public static function oauth(bool $getShared = true): \OneShot\Auth\Services\OAuthService
    {
        if ($getShared) {
            return static::getSharedInstance('oauth');
        }

        return new \OneShot\Auth\Services\OAuthService();
    }
}
