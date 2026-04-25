<?php

namespace OneShot\Keys\Controllers;

use OneShot\Keys\Models\ApiKey;
use OneShot\Keys\Models\Usage;

abstract class Keys extends \OneShot\Core\Controllers\App
{
    protected ApiKey $apiKeyModel;
    protected Usage  $usageModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->apiKeyModel = new ApiKey();
        $this->usageModel  = new Usage();
        $this->appendBC(__('keys.title', 'API Keys'), route_to('app.keys'));
    }

    protected function loadOwnedKey(string $signedHash): object
    {
        $id  = signedId($signedHash);
        $key = $id ? $this->apiKeyModel->findByIdForUser((int)$id, (int)$this->userId()) : null;

        if ($key === null) {
            $this->redirectWith('app.keys', 'error', __('keys.not_found', 'Key not found.'));
            exit;
        }

        return $key;
    }

    protected function userId(): int
    {
        return (int)session()->get('user_id');
    }
}
