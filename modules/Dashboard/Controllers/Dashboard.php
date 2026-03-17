<?php

namespace Modules\Dashboard\Controllers;

use OneShot\Core\Controllers\App;

class Dashboard extends App
{
    public function index(): string
    {
        $this->appendBC(__('dashboard.title', 'Dashboard'), '');
        $this->share('role', session('user_role'));

        return $this->render('Dashboard::app/index');
    }

}
