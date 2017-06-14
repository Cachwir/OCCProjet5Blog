<?php
/**
 * Author: Stanislas Oger <stanislas.oger@gmail.com>
 * Contributor: Cachwir <cachwir@gmail.com>
 */

namespace src\app;

use lib\App;
use src\controllers\FrontController;

class MyApp extends App {

    protected static $controllers = [
        "default" => FrontController::class,
    ];
}