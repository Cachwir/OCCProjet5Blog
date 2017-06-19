<?php

use lib\Config;
use src\app\MyApp;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../lib/functions.php';
require_once __DIR__.'/../src/config/config.php';

if (Config::get()['debug']) {
    require_once __DIR__.'/../lib/debug.php';
}

/** @var MyApp $App */
$App = MyApp::getInstance();
$App->run();