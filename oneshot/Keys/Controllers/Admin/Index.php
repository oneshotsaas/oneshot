<?php

namespace OneShot\Keys\Controllers\Admin;

use OneShot\Keys\Models\ApiKey;
use OneShot\Keys\Models\Usage;

class Index extends \OneShot\Core\Controllers\Admin
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
        $this->appendBC(__('keys.title', 'API Keys'), route_to('admin.keys'));
    }

    public function index(): string
    {
        $db = \Config\Database::connect();
        $keys = $db->query(
            'SELECT k.*, u.email AS user_email
             FROM keys_keys k
             JOIN auth_users u ON u.id = k.user_id
             WHERE k.deleted_at IS NULL
             ORDER BY k.created_at DESC'
        )->getResult();

        return $this->render('Keys::admin/index', compact('keys'));
    }
}
