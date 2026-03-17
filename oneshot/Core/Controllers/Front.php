<?php

namespace OneShot\Core\Controllers;

abstract class Front extends Base
{
    protected string $layout = 'Core::layouts/front';

    protected function setMeta(string $title, string $desc = '', string $og = ''): void
    {
        $this->share('meta_title', $title);
        $this->share('meta_desc', $desc);
        $this->share('meta_og', $og);
    }
}
