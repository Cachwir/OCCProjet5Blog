<?php

require_once __DIR__.'/../lib/Config.php';
require_once __DIR__.'/config/config.php';
if (Config::get()['debug']) {
	require_once __DIR__.'/../lib/debug.php';
}
require_once __DIR__.'/../lib/functions.php';
require_once __DIR__.'/../lib/ORM.php';
require_once __DIR__.'/../lib/PDOWrapper.php';
require_once __DIR__.'/../lib/Form.php';
require_once __DIR__.'/../lib/App.php';
require_once __DIR__.'/../lib/Mobile_Detect.php';
require_once __DIR__.'/../lib/BotDetect.php';
require_once __DIR__.'/../lib/Inflector.php';
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/app/Controller.php';
require_once __DIR__.'/data/BlogPost.php';
require_once __DIR__.'/utilitaries/Time.php';

ensure_session_started();