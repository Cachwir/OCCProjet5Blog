<?php
/**
 * Author: Stanislas Oger <stanislas.oger@gmail.com>
 * Contributor: Cachwir <cachwir@gmail.com>
 */

namespace lib;

class App {

    use Singleton;

    protected $Controller;

	public function __construct() {
	    // Initializes the Session and Request
		Session::init();
		Request::init();

		// if debug
        $config = Config::get();
        if ($config['debug'] && Request::get('debug') == $config['debug_key']) {
            Session::set('debug', true);
        }
	}
}