<?php

namespace OneShot\Keys\Controllers\App;

use OneShot\Keys\Controllers\Keys;
use OneShot\Keys\Services\KeyService;

class Index extends Keys
{
    public function index(): string
    {
        $keys = $this->apiKeyModel->forUser($this->userId());

        // Attach usage stats to each key
        foreach ($keys as $key) {
            $key->usage = $this->usageModel->sumForKey($key->id, 0);
        }

        $this->share('page_actions_view', 'Keys::app/_actions');
        return $this->render('Keys::app/index', compact('keys'));
    }

    public function create(): string
    {
        $this->appendBC(__('keys.create', 'Create Key'), route_to('app.keys.create'));
        $this->share('extra_scripts', render('Keys::app/_scripts'));
        return $this->render('Keys::app/create');
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'name' => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $name = $this->request->getPost('name');

        // Parse limits
        $limitsRequests = $this->parseLimits('limits_requests');
        $limitsCredits  = $this->parseLimits('limits_credits');

        // Parse expire
        $expires = $this->parseExpire();

        $result = (new KeyService())->generate(
            $this->userId(),
            $name,
            $limitsRequests,
            $limitsCredits,
            $expires
        );

        session()->setFlashdata('new_key', $result['raw']);
        return redirect()->to(route_to('app.keys.show', signId($result['id'])));
    }

    public function show(string $hash): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $key     = $this->loadOwnedKey($hash);
        $new_key = session()->getFlashdata('new_key');

        if (empty($new_key)) {
            return redirect()->to(route_to('app.keys'));
        }

        $this->appendBC($key->name);
        $this->share('extra_scripts', render('Keys::app/_scripts'));
        return $this->render('Keys::app/show', compact('key', 'new_key'));
    }

    public function edit(string $hash): string
    {
        $key = $this->loadOwnedKey($hash);
        $this->appendBC($key->name);
        $this->share('extra_scripts', render('Keys::app/_scripts'));
        return $this->render('Keys::app/edit', compact('key'));
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $key = $this->loadOwnedKey($hash);

        $rules = [
            'name' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $data = [];

        $name = $this->request->getPost('name');
        if (!empty($name)) {
            $data['name'] = $name;
        }

        $data['limits_requests'] = json_encode($this->parseLimits('limits_requests'));
        $data['limits_credits']  = json_encode($this->parseLimits('limits_credits'));

        $expires = $this->parseExpire();
        if ($expires === null) {
            $data['expires_at'] = null;
        } elseif (is_int($expires)) {
            $data['expires_at'] = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->modify("+{$expires} days")
                ->format('Y-m-d H:i:s');
        } else {
            $data['expires_at'] = (new \DateTimeImmutable($expires, new \DateTimeZone('UTC')))->format('Y-m-d 00:00:00');
        }

        $this->apiKeyModel->save(['id' => $key->id] + $data);

        session()->setFlashdata('success', __('keys.updated', 'Key updated.'));
        return redirect()->to(route_to('app.keys'));
    }

    public function toggle(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $key    = $this->loadOwnedKey($hash);
        $status = $key->status === 'active' ? 'inactive' : 'active';
        $this->apiKeyModel->save(['id' => $key->id, 'status' => $status]);
        session()->setFlashdata('success', __('keys.toggled', 'Key status updated.'));
        return redirect()->to(route_to('app.keys'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $key = $this->loadOwnedKey($hash);
        $this->apiKeyModel->delete($key->id);
        session()->setFlashdata('success', __('keys.deleted', 'Key deleted.'));
        return redirect()->to(route_to('app.keys'));
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function parseLimits(string $field): array
    {
        $days = $this->request->getPost("{$field}[days]") ?? [];
        $max  = $this->request->getPost("{$field}[max]")  ?? [];
        $out  = [];
        foreach ((array)$days as $i => $d) {
            $m = (int)($max[$i] ?? 0);
            if ($m > 0) {
                $out[] = ['days' => (int)$d, 'max' => $m];
            }
        }
        return $out;
    }

    private function parseExpire(): int|string|null
    {
        $type = $this->request->getPost('expire_type');
        if ($type === 'days') {
            $days = (int)$this->request->getPost('expire_days');
            return $days > 0 ? $days : null;
        }
        if ($type === 'date') {
            $date = $this->request->getPost('expire_date');
            return !empty($date) ? $date : null;
        }
        return null;
    }
}
