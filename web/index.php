<?php

use lib\Config;
use src\app\MyApp;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../lib/functions.php';
require_once __DIR__.'/../src/config/config.php';

if (Config::get()['debug']) {
    require_once __DIR__.'/../lib/debug.php';
}

/** @var MyApp $App */
$App = MyApp::getInstance();
$App->run();