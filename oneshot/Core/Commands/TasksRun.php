<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OneShot\Core\Services\TaskRunner;

class TasksRun extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'tasks:run';
    protected $description = 'Process pending tasks batch (email/telegram delivery queue)';

    public function run(array $params): void
    {
        $limit  = (int)($params[0] ?? 50);
        $runner = new TaskRunner();
        $stats  = $runner->runBatch($limit);

        CLI::write(sprintf(
            'Tasks processed: %d done, %d retrying, %d failed permanently',
            $stats['done'],
            $stats['retrying'],
            $stats['failed']
        ), 'green');
    }
}
