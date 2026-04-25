<?php

namespace OneShot\Activity\Controllers\Admin;

use OneShot\Core\Controllers\Admin;
use OneShot\Activity\Models\Log;

class Activity extends Admin
{
    public function index(): string
    {
        $model = new Log();

        $userId    = $this->request->getGet('user_id');
        $action    = $this->request->getGet('action');
        $dateFrom  = $this->request->getGet('date_from');
        $dateTo    = $this->request->getGet('date_to');

        if ($userId)   $model->where('user_id', (int)$userId);
        if ($action)   $model->like('action', $action, 'both');
        if ($dateFrom) $model->where('created_at >=', $dateFrom);
        if ($dateTo)   $model->where('created_at <=', $dateTo . ' 23:59:59');

        $logs = $model->orderBy('id', 'DESC')->paginate(50);

        $this->appendBC(__('activity.activity_log', 'Activity Log'), route_to('admin.activity'));
        $this->share('page_actions_view', 'Activity::admin/_filters');

        return $this->render('Activity::admin/index', [
            'logs'    => $logs,
            'pager'   => $model->pager,
            'filters' => ['userId' => $userId, 'action' => $action, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo],
        ]);
    }
}
