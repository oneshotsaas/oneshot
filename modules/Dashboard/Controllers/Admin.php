<?php

namespace Modules\Dashboard\Controllers;

use OneShot\Core\Controllers\Admin as AdminController;

class Admin extends AdminController
{
    public function index(): string
    {
        $this->appendBC(__('dashboard.title', 'Dashboard'), '');

        return $this->render('Dashboard::admin/index');
    }
}
