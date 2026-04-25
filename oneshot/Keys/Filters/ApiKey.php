<?php

namespace OneShot\Keys\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use OneShot\Keys\Services\KeyService;

class ApiKey implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $rawKey = $request->getHeaderLine('X-API-Key');

        if (empty($rawKey)) {
            $auth = $request->getHeaderLine('Authorization');
            if (str_starts_with($auth, 'Bearer ')) {
                $rawKey = substr($auth, 7);
            }
        }

        if (empty($rawKey)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'code' => 401, 'message' => 'Unauthorized']);
        }

        $credits = isset($arguments[0]) ? (int)$arguments[0] : 0;

        $result = (new KeyService())->validateAndTrack($rawKey, $credits);

        if (!empty($result->error)) {
            return service('response')
                ->setStatusCode($result->status)
                ->setJSON(['success' => false, 'code' => $result->status, 'message' => $result->message]);
        }

        $_SERVER['KEYS_KEY_ID'] = $result->id;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
