<?php

namespace OneShot\Core\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use OneShot\Core\Models\Base as BaseModel;

abstract class Api extends Base
{
    protected array  $public    = [];
    protected string $modelClass = '';
    protected array  $fields    = [];
    protected array  $editable  = [];
    protected string $resource  = 'item';
    protected string $resources = 'items';

    private ?BaseModel $modelInstance = null;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $method = service('router')->methodName();
        if (! in_array($method, $this->public, true)) {
            $this->checkToken();
        }
    }

    protected function checkToken(): void
    {
        if (!empty($_SERVER['KEYS_KEY_ID'])) {
            return;
        }

        $raw = $this->request->getHeaderLine('X-API-Key');

        if (empty($raw)) {
            $auth = $this->request->getHeaderLine('Authorization');
            if (str_starts_with($auth, 'Bearer ')) {
                $raw = substr($auth, 7);
            }
        }

        $result = empty($raw) ? null : (new \OneShot\Keys\Services\KeyService())->validateAndTrack($raw);

        if ($result === null || !empty($result->error)) {
            $status  = $result->status  ?? 401;
            $message = $result->message ?? 'Unauthorized';
            $this->fail($message, $status, ['code' => $status])->send();
            exit;
        }
    }

    // --- CRUD helpers ---

    protected function model(): BaseModel
    {
        if ($this->modelInstance === null) {
            $this->modelInstance = new $this->modelClass();
        }
        return $this->modelInstance;
    }

    protected function serialize(object $item): array
    {
        $result = [];
        foreach ($this->fields as $field) {
            $result[$field] = $item->{$field} ?? null;
        }
        return $result;
    }

    protected function getLimit(int $max = 100, int $default = 20): int
    {
        return max(1, min($max, (int) ($this->request->getGet('limit') ?? $default)));
    }

    protected function getOffset(): int
    {
        return max(0, (int) ($this->request->getGet('offset') ?? 0));
    }

    // --- Generic CRUD actions (override as needed) ---

    public function index(): ResponseInterface
    {
        $limit  = $this->getLimit();
        $offset = $this->getOffset();
        $items  = $this->model()->findAll($limit, $offset);
        $total  = $this->model()->countAllResults(false);

        return $this->ok([
            $this->resources => array_map([$this, 'serialize'], $items),
            'total'          => $total,
            'limit'          => $limit,
            'offset'         => $offset,
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        $item = $this->model()->getById($id);

        if ($item === null) {
            return $this->fail(__('core.not_found', 'Not found.'), 404);
        }

        return $this->ok([$this->resource => $this->serialize($item)]);
    }

    public function update(int $id): ResponseInterface
    {
        $item = $this->model()->getById($id);

        if ($item === null) {
            return $this->fail(__('core.not_found', 'Not found.'), 404);
        }

        $input = $this->request->getJSON(true) ?: $this->request->getPost();
        $data  = [];

        foreach ($this->editable as $field) {
            if (array_key_exists($field, $input)) {
                $data[$field] = $input[$field] === '' ? null : $input[$field];
            }
        }

        if (empty($data)) {
            return $this->fail(__('core.nothing_to_update', 'Nothing to update.'), 400);
        }

        $this->model()->save(array_merge($data, ['id' => $id]));

        return $this->ok([$this->resource => $this->serialize($this->model()->getById($id))]);
    }

    public function delete(int $id): ResponseInterface
    {
        $item = $this->model()->getById($id);

        if ($item === null) {
            return $this->fail(__('core.not_found', 'Not found.'), 404);
        }

        $this->model()->delete($id);

        return $this->ok();
    }

    // --- Response helpers ---

    protected function ok(mixed $data = null, int $code = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON(['success' => true, 'data' => $data]);
    }

    protected function fail(string $msg, int $code = 400, mixed $errors = null): ResponseInterface
    {
        $body = ['success' => false, 'message' => $msg];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return $this->response
            ->setStatusCode($code)
            ->setJSON($body);
    }
}
