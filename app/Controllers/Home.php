<?php

namespace App\Controllers;

class Home extends \OneShot\Core\Controllers\Front
{
    public function index(): string
    {
        return $this->render('home/index');
    }
}
