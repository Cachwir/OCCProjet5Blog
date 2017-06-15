<?php

namespace lib;

use Twig_Environment;
use Twig_Loader_Filesystem;

class BasicController {

    use Singleton;

	public static $pages = []; // pages using the default layout
    public static $full_pages = [ // pages that have their own layout
    ];
    public static $error_pages = [ // pages used for error handling
        500 => null,
        404 => null,
    ];

    /** @var Twig_Environment $Twig */
    protected $Twig;
    /** @var App $App */
	protected $App;

	public function __construct(App $App) {
		$this->App = $App;
		$this->initTwig();
	}

	public function initTwig() {
        $Loader = new Twig_Loader_Filesystem(Config::getTemplateDir(Config::getDefaultLang()));
        $this->Twig = new Twig_Environment($Loader, array(
            'debug' => $this->isDebug(),
            'cache' => __DIR__ . '/../var/cache',
        ));
    }

    public function serve() {

        $page = null;
        $pages = self::getPages();

        try {
            // arbitrary page navigation
            if ($this->getPage() !== null) {

                if (!in_array($this->getPage(), $pages)) {
                    if ($this->isDebug()) {
                        throw new \Exception("Unknown page. Valid pages: " . join(', ', $pages), 404);
                    }
                }

                $this->setCurrentPage($this->getPage());
                $page = $this->getPage();
            }

            $Controller = $this; // for accessing $Controller in the header and footer templates
            $this->Twig->addGlobal("Controller", $Controller);

            $content = $this->callAction($this->getNextPage($page, Request::isAjax()));
        } catch (\Exception $e) {
            if (Config::get()['debug']) {
                throw $e;
            } else {
                // exception handling if not in debug mode
                $code = array_key_exists($e->getCode(), self::$error_pages) ? $e->getCode() : "404"; // if a page exist for this code

                if (in_array($code, static::$error_pages)) { // if a page error has been set, it should be displayed instead of the default message
                    $content = $this->callAction($code);
                } else {
                    $content = '<div class="general-error">Une erreur est survenue, veuillez réessayer plus tard.</div>';
                }
            }
        }

        if (Request::isAjax()) {
            return json_encode([
                'page'    => $this->getCurrentPage(),
                'content' => $content,
            ]);
        } else {
            // Fix IE compatibility (http://stackoverflow.com/questions/3449286/force-ie-compatibility-mode-off-using-tags)
            header('X-UA-Compatible: IE=edge');

            if (in_array($this->getCurrentPage(), static::$full_pages)) {
                return $content;
            } else {
                return $this->Twig->render('layout.html.twig', ['content' => $content]);
            }
        }
    }

    public function getApp() {
	    return $this->App;
    }

    public function getConfig() {
	    return Config::getLocaleConfig();
    }

	public function getPage() {
	    return Request::get('page');
    }

    public static function getPages() {
	    return array_merge(static::$pages, static::$full_pages, static::$error_pages);
    }

    public function getParams() {
	    return Request::getParams();
    }

    public function isDebug() {
	    return Config::get()["debug"];
    }

	protected function render($template, $params = []) {

		// set template variables
		foreach ($params as $name => $value) {
			$$name = $value;
		}

		ob_start();
        echo $this->Twig->render('pages/'.$template.'.html.twig', $params);
		return ob_get_clean();
	}

	public function redirect($url) {
		header('location: '.$url);
		exit();
	}

	public function redirectToPage($action, $params = [], $controller = null) {
		$params['page'] = $action;
		$target = '?' . http_build_query($params);
		$controller_path = $this->App->getControllerPath($controller);
		$controller_path = $controller_path === null ? "" : ("/" . $controller_path);
		header('location: ' . $controller_path . $target);
		exit();
	}

    /**
     * Call an action inside the current controller
     *
     * @param $action
     * @param null $method
     * @param null $params
     * @return mixed
     * @throws \Exception
     */
    protected function callAction($action, $method = null, $params = null) {
        if ($params !== null) Request::setParams($params);
        if ($method !== null) Request::setMethod($method);

        $method = camelize($action)."Action";
        if (!method_exists($this, $method)) {
            throw new \Exception("Action inconnue: $method", 404);
        }

        return call_user_func([$this, $method], $params);
    }

    /**
     * Asks the App to serve the response of a specified action. Useful for discrete redirection and multi-views serving.
     *
     * @param   $action
     * @param   null $method
     * @param   null $params
     * @param   string  $controller  The controller's class name
     * @return  mixed
     */
	protected function forward($action, $method = null, $params = null, $controller = null) {
		if ($params !== null) Request::setParams($params);
		if ($method !== null) Request::setMethod($method);

        $controller = $controller === null ? get_class($this) : $controller;

        return $this->App->forward($controller, $action);
	}

	public function getCurrentPage() {
		$page = Session::get('_current_page');
		$pages = self::getPages();

		if (!in_array($page, $pages)) {
			$page = $pages[0];
			$this->setCurrentPage($page);
		}

		return $page;
	}

	public function setCurrentPage($page) {
		$this->setNextPage(null); // reset the next page
		return Session::set('_current_page', $page);
	}

	public function setNextPage($page) {
		return Session::set('_next_page', $page);
	}

	public function getNextPage($page, $is_ajax) {
		if (!$is_ajax) {
			if (!in_array($page, static::$pages)) {
				$page = static::$pages[0];
				$this->setCurrentPage($page);
			} else {
				$this->setCurrentPage($page);
			}

			return $page;
		} else {
			$page = Session::get('_next_page');
			if ($page !== null) {
				return $page;
			} else {
				return $this->getCurrentPage();
			}
		}
	}

    /**
     * Configures a form and feeds the given $Entity with the form data if validated. Also, renders the page and forwards
     * to the next page
     *
     * @param ORM       $Entity                 The entity which is going to be fed. If you want a field not to be filled automatically, put it in the Entity's $computed_fields
     * @param string    $page                   The page to render
     * @param string    $next                   The page to forward/redirect if the form is validated
     * @param array     $next_params            The next page's params
     * @param array     $fields                 An array of form fields (name, type and callable validator)
     * @param array     $template_params        The page's params
     * @param callable  $form_validator         A callable global validator
     * @param callable  $on_post_success        A callback if the form is validated
     * @return mixed|string
     */
    public function formStepAction(Form $Form, $page, $next, $next_params, $template_params = [], $on_post_success = null) {

        /** @var array|ORM $data */
        $data = $Form->getData();
        $template_params['data'] = $Form->getData();

        if (Request::getMethod() == 'post') {
            $Form->bind(Request::getParams());

            if ($Form->isValid()) {
                if ($data instanceof ORM) {
                    $data->save();
                }

                if (is_callable($on_post_success)) {
                    $on_post_success($Form, $next_params);
                }

                if (!Request::isAjax()) {
                    $this->redirectToPage($next, $next_params);
                } else  {
                    return $this->forward($next, 'get', $next_params);
                }
            }
        }

        return $this->render($page, $template_params + [
                'Form' => $Form,
            ]);
    }
}


