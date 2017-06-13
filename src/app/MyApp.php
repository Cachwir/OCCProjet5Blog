<?php
/**
 * Author: Stanislas Oger <stanislas.oger@gmail.com>
 * Contributor: Cachwir <cachwir@gmail.com>
 */

namespace src\app;

use lib\App;
use src\controllers\Controller;

class MyApp extends App {

    public function run()
    {
        $this->Controller = Controller::getInstance([$this]);
        echo $this->Controller->serve();
    }
}


