<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Core\Controllers\Api;
use OneShot\Billing\Services\BillingService;

class Webhook extends Api
{
    public function handle(string $provider, string $token): \CodeIgniter\HTTP\ResponseInterface
    {
        // URL token check — per-provider secret
        $expectedToken = option("billing.webhook_secret_{$provider}", '');
        if (empty($expectedToken) || !hash_equals($expectedToken, $token)) {
            return $this->response->setStatusCode(404);
        }

        $rawPayload = $this->request->getBody();
        $billing    = new BillingService();

        // Signature verification + event parsing per provider
        $handlerClass = 'OneShot\\Billing\\Webhooks\\' . ucfirst($provider) . 'Handler';
        if (!class_exists($handlerClass)) {
            l(['provider' => $provider, 'error' => 'no handler'], 'billing_webhook');
            return $this->response->setStatusCode(400);
        }

        $handler = new $handlerClass($rawPayload, $this->request);

        try {
            $handler->verifySignature();
        } catch (\Exception $e) {
            l(['provider' => $provider, 'error' => 'signature_failed', 'msg' => $e->getMessage()], 'billing_webhook');
            return $this->response->setStatusCode(400);
        }

        $eventId   = $handler->getEventId();
        $eventType = $handler->getEventType();

        l(['provider' => $provider, 'event' => $eventType, 'id' => $eventId], 'billing_webhook');

        // Claim ownership — handles dedup, concurrent workers, payload mismatch
        $claimToken = $billing->claimWebhookEvent($provider, $eventId, $eventType, $rawPayload);
        if ($claimToken === false) {
            return $this->ok(['status' => 'skipped']);
        }

        try {
            $handler->dispatch($billing);
            $billing->markWebhookProcessed($provider, $eventId);
        } catch (\Exception $e) {
            l(['provider' => $provider, 'event' => $eventType, 'error' => $e->getMessage()], 'billing_webhook');
            // Do NOT mark processed — let retry handle it (status stays 'processing', cron will reset to 'failed')
            return $this->response->setStatusCode(500);
        }

        return $this->ok(['status' => 'ok']);
    }
}
