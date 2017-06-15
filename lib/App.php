<?php
/**
 * Author: Stanislas Oger <stanislas.oger@gmail.com>
 * Contributor: Cachwir <cachwir@gmail.com>
 */

namespace lib;

class App {

    use Singleton;

    protected static $controllers = [
        "default"   => BasicController::class,
    ];

    protected $controller;

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

    public function run()
    {
        $redirect_url = isset($_SERVER['REDIRECT_URL']) ? substr($_SERVER['REDIRECT_URL'],1) : null; // substr to get rid of the first "/"

        if (empty($redirect_url)) {
            $this->controller = static::$controllers["default"];
        } else {
            $path = explode("/", $redirect_url);
            // the first string element in the redirect url shall be the controller if it exists
            $this->controller = array_key_exists($path[0], static::$controllers) ? static::$controllers[$path[0]] : static::$controllers["default"];
        }

        $Controller = call_user_func($this->controller . "::getInstance", [$this]);
        echo $Controller->serve();
    }

    public function forward($controller, $action)
    {
        Request::set("page", $action);
        $past_controller = $this->controller;
        $this->controller = $controller;

        $Controller = call_user_func($this->controller . "::getInstance", [$this]);
        $response = $Controller->serve();

        $this->controller = $past_controller;
        return $response;
    }

    /**
     * @return  mixed|null  The controller key (used in the url)
     */
    public function getControllerPath($controller = null) {
        $controller = $controller === null || !in_array($controller, static::$controllers) ? $this->controller : $controller;
	    $request_controller = array_search($controller, static::$controllers);
	    return $request_controller == "default" ? null : $request_controller;
    }
}