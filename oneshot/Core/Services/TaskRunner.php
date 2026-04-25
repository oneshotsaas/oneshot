<?php

namespace OneShot\Core\Services;

use OneShot\Core\Models\Task;

class TaskRunner
{
    private Task $model;

    public function __construct()
    {
        $this->model = new Task();
    }

    public function runOne(int $id): void
    {
        $db = $this->model->db;

        // Atomic claim: only run if status is pending
        $affected = $db->query(
            "UPDATE tasks SET status='running', claimed_at=NOW() WHERE id=? AND status='pending'",
            [$id]
        );
        if ($db->affectedRows() === 0) {
            return; // already running or done
        }

        $task = $this->model->getById($id);
        if (!$task) return;

        $this->execute($task);
    }

    public function runBatch(int $limit = 50): array
    {
        $db  = $this->model->db;
        $now = date('Y-m-d H:i:s');

        // Fetch candidates (pending ready + stale running > 10 min)
        $rows = $db->query(
            "SELECT id FROM tasks
             WHERE (status = 'pending' AND (retry_after IS NULL OR retry_after <= ?))
                OR (status = 'running' AND claimed_at < DATE_SUB(?, INTERVAL 10 MINUTE))
             LIMIT ?",
            [$now, $now, $limit]
        )->getResultArray();

        if (empty($rows)) return ['done' => 0, 'retrying' => 0, 'failed' => 0];

        $ids = array_column($rows, 'id');

        // Atomic claim
        $db->query(
            'UPDATE tasks SET status=\'running\', claimed_at=NOW() WHERE id IN (' . implode(',', $ids) . ') AND status IN (\'pending\', \'running\')'
        );

        $stats = ['done' => 0, 'retrying' => 0, 'failed' => 0];
        foreach ($ids as $id) {
            $task = $this->model->getById($id);
            if (!$task) continue;
            $result = $this->execute($task);
            $stats[$result]++;
        }

        return $stats;
    }

    private function execute(object $task): string
    {
        $payload = json_decode($task->payload ?? '{}', true) ?: [];

        try {
            $result = match ($task->type) {
                'notification.email'    => service('notify', 'email')
                                                ->send($payload['to'] ?? '', $payload['body'] ?? '', ['subject' => $payload['title'] ?? '']),
                'notification.telegram' => service('notify', 'telegram')
                                                ->send($payload['chat_id'] ?? '', $payload['text'] ?? ''),
                default => throw new \RuntimeException('Unknown task type: ' . $task->type),
            };

            if ($result === false) {
                throw new \RuntimeException('Delivery returned false');
            }

            $this->model->update($task->id, [
                'status'       => 'done',
                'processed_at' => date('Y-m-d H:i:s'),
                'error'        => null,
            ]);

            return 'done';
        } catch (\Throwable $e) {
            $attempts = (int)$task->attempts + 1;
            $max      = (int)($task->max_attempts ?? 3);

            if ($attempts >= $max) {
                $this->model->update($task->id, [
                    'status'       => 'failed',
                    'attempts'     => $attempts,
                    'error'        => $e->getMessage(),
                    'processed_at' => date('Y-m-d H:i:s'),
                ]);
                return 'failed';
            }

            // Exponential backoff: attempts² × 60 seconds
            $retryAfter = date('Y-m-d H:i:s', time() + ($attempts ** 2 * 60));
            $this->model->update($task->id, [
                'status'      => 'pending',
                'attempts'    => $attempts,
                'error'       => $e->getMessage(),
                'retry_after' => $retryAfter,
                'claimed_at'  => null,
            ]);
            return 'retrying';
        }
    }
}
