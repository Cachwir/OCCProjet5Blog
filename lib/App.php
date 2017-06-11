<?php
/**
 * Author: Stanislas Oger <stanislas.oger@gmail.com>
 * Contributor: Cachwir <cachwir@gmail.com>
 */

namespace lib;

use Twig_Environment;
use Twig_Loader_Filesystem;

class App {

	public static $pages = []; // pages that can be freely accessed with ?page=
    public static $fullPages = [ // pages that have their own header and footer
    ];
	private static $Instance = null;

	/**
	 * @return App
	 */
	public static function get() {
		if (!self::$Instance instanceof App) {
			self::$Instance = new static();
		}
		return self::$Instance;
	}

	protected $params;
	protected $method;
    protected $ip;
    protected $userAgent;
    protected $is_ajax;

    protected $Twig;

	public function __construct() {
		ensure_session_started();
	}

    public function run() {
        $this->parseRequest();

        $this->is_ajax = $this->method == 'post' && isset($this->params['mode']) && $this->params['mode'] == 'ajax';
        $config = $this->getConfig();

        if ($config['debug'] && isset($this->params['debug']) && $this->params['debug'] == $config['debug_key']) {
            $this->sessionSet('debug', true);
        }

        $page = null;

        // arbitrary page navigation
        if (isset($this->params['page'])) {

            if (!in_array($this->params['page'], static::$pages)) {
                if ($this->sessionGet('debug')) {
                    echo "Unknown page. Valid pages: " . join(', ', static::$pages);
                    die;
                }
            }

            $this->setCurrentPage($this->params['page']);
            $page = $this->params['page'];
        }

        $App = $this; // for accessing $app in the header and footer templates

        $Loader = new Twig_Loader_Filesystem(Config::getTemplateDir($this->getLang()));
        $this->Twig = new Twig_Environment($Loader, array(
            'debug' => $this->getConfig()['debug'],
            'cache' => __DIR__ . '/../var/cache',
        ));
        $this->Twig->addGlobal("App", $App);

        try {
            $content = $this->forward($this->getNextPage($page, $this->is_ajax));
        } catch (\Exception $e) {
            if ($config['debug']) {
                throw $e;
            } else {
                $content = '<div class="general-error">Une erreur est survenue, veuillez réessayer plus tard.</div>';
            }
        }

        if ($this->is_ajax) {
            echo json_encode([
                'page'    => $this->getCurrentPage(),
                'content' => $content,
            ]);
        } else {
            // Fix IE compatibility (http://stackoverflow.com/questions/3449286/force-ie-compatibility-mode-off-using-tags)
            header('X-UA-Compatible: IE=edge');

            if (in_array($this->getCurrentPage(), static::$fullPages)) {
                echo $content;
            } else {
                echo $this->Twig->render('header.html.twig');
                echo $content;
                echo $this->Twig->render('footer.html.twig');
            }
        }
    }

	public function getConfig() {
		return Config::get($this->getLang());
	}

	public function getLang() {
		return Config::getDefaultLang();
	}

	public function parseRequest() {
		$this->params        = $_POST + $_GET;
		$this->method        = strtolower($_SERVER['REQUEST_METHOD']);
		$this->ip            = $_SERVER['REMOTE_ADDR'];
		$this->userAgent     = null;

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}
	}

	protected function render($template, $params = []) {
		if (!in_array($template, static::$pages)) {
			return $this->error("Page introuvable.", 404);
		}

		// set template variables
		foreach ($params as $name => $value) {
			$$name = $value;
		}

		ob_start();
        echo $this->Twig->render('pages/'.$template.'.html.twig', $params);
		return ob_get_clean();
	}

	public function error($message, $code) {
		return $message;
	}

	public function redirect($url) {
		header('location: '.$url);
		exit();
	}

	public function redirectToPage($action, $params = []) {
		$params['page'] = $action;
		$target = '?' . http_build_query($params);
		header('location: ' . $target);
		exit();
	}

	protected function forward($action, $method = null, $params = null) {
		if ($params !== null) $this->params = $params;
		if ($method !== null) $this->method = $method;

		$method = camelize($action)."Action";
		if (!method_exists($this, $method)) {
			throw new \Exception("Action inconnue: $method");
		}

		return call_user_func([$this, $method], $params);
	}

	public function paramGet($field, $default = null) {
	    return isset($this->params[$field]) ? $this->params[$field] : $default;
    }

	public function sessionGet($field, $default = null) {
		$ns = $this->getConfig()['session_namespace'];
		return isset($_SESSION[$ns][$field]) ? $_SESSION[$ns][$field] : $default;
	}

	public function sessionSet($field, $value) {
		$ns = $this->getConfig()['session_namespace'];
		return $_SESSION[$ns][$field] = $value;
	}


	public function getCurrentPage() {
		$page = $this->sessionGet('_current_page');

		if (!in_array($page, static::$pages)) {
			$page = static::$pages[0];
			$this->setCurrentPage($page);
		}

		return $page;
	}

	public function setCurrentPage($page) {
		$this->setNextPage(null); // reset the next page
		return $this->sessionSet('_current_page', $page);
	}

	public function setNextPage($page) {
		return $this->sessionSet('_next_page', $page);
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
			$page = $this->sessionGet('_next_page');
			if ($page !== null) {
				return $page;
			} else {
				return $this->getCurrentPage();
			}
		}
	}


	public function createForm($data = [], $name = null) {
		$form = new Form($data, $name);
		$form->setAction($this->getConfig()['ajaxController']);
		return $form;
	}


	public function setCaptchaKey($form_name, $key) {
		$keys = $this->sessionGet('CAPTCHA_KEYS', []);
		$keys[$form_name][] = $key;
		if (count($keys[$form_name]) > $this->getConfig()['csrf_token_validity']) { // because the infrastructure may perform additional GET requests, we need to store the last N captcha codes
			array_shift($keys[$form_name]);
		}
		$this->sessionSet('CAPTCHA_KEYS', $keys);

	}

	public function isCaptchaKeyValid($form_name, $key) {
		$keys = $this->sessionGet('CAPTCHA_KEYS', []);
		return isset($keys[$form_name]) && in_array($key, $keys[$form_name]);
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
    public function formStepAction(ORM $Entity, $page, $next, $next_params, $fields, $template_params = [], $form_validator = null, $on_post_success = null) {

        $template_params['Entity'] = $Entity;

        $Form = $this->createForm($Entity);
        $Form->setAttr('class', $page);

        foreach ($fields as $field) {
            $Form->add($field[0], $field[1], isset($field[2]) ? $field[2] : null);
        }

        if (is_callable($form_validator)) {
            $Form->addValidator($form_validator);
        }

        if ($this->method == 'post') {
            $Form->bind($this->params);

            if ($Form->isValid()) {
                $Entity->save();

                if (is_callable($on_post_success)) {
                    $on_post_success($Form, $next_params);
                }

                if (!$this->is_ajax) {
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


