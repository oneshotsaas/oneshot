<?php

namespace OneShot\Notifications\Controllers\App;

class Inbox extends Notifications
{
    public function index(): string
    {
        $userId = (int)session('user_id');
        $model  = new \OneShot\Notifications\Models\Notification();
        $items  = $model->where('user_id', $userId)->orderBy('created_at', 'DESC')->paginate(20);

        $this->appendBC(__('notifications.notifications', 'Notifications'), route_to('app.notifications'));
        $this->share('page_actions_view', 'Notifications::app/_actions');

        return $this->render('Notifications::app/index', [
            'notifications' => $items,
            'pager'         => $model->pager,
        ]);
    }

    public function markRead(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX only']);
        }
        $this->notifier->markRead((int)session('user_id'), $id);
        return $this->response->setJSON(['ok' => true]);
    }

    public function markAllRead(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX only']);
        }
        $this->notifier->markAllRead((int)session('user_id'));
        return $this->response->setJSON(['ok' => true]);
    }

    public function updatePreference(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX only']);
        }

        $body    = json_decode($this->request->getBody(), true) ?? [];
        $type    = $body['type']    ?? '';
        $channel = $body['channel'] ?? '';
        $enabled = (bool)($body['enabled'] ?? false);

        $cfg = config('NotificationTypes')->types[$type] ?? null;
        if (!$cfg || !in_array($channel, $cfg['channels'], true)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Unknown type or channel']);
        }

        $this->notifier->updatePreference((int)session('user_id'), $type, $channel, $enabled);
        return $this->response->setJSON(['ok' => true]);
    }
}
