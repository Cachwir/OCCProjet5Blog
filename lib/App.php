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
        $Config = Config::get();
        if ($Config['debug'] && Request::get('debug') == $Config['debug_key']) {
            Session::set('debug', true);
        }
	}
}