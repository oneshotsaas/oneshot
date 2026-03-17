<?php

use CodeIgniter\Events\Events;

Events::on('pre_system', [\OneShot\Core\Loader::class, 'boot']);
