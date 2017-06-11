<?php

use lib\Config;
use src\app\AppController;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../lib/functions.php';
require_once __DIR__.'/../src/config/config.php';

if (Config::get()['debug']) {
    require_once __DIR__.'/../lib/debug.php';
}

/** @var AppController $App */
$App = AppController::get();
$App->run();