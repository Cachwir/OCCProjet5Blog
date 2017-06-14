<?php
/**
 * User: stan
 * Date: 3/18/15
 * Time: 2:46 PM
 */

namespace lib;

class Form {

	protected $data;
	protected $fields = [];
	protected $fieldValidators = [];
	protected $validators = [];
	protected $messages = ['error' => [], 'warning' => [], 'success' => []];
	protected $attr = [];
	protected $action = '';

	/**
	 * @param array|Object $data
	 */
	public function __construct($data = [], $name = null) {
		$this->data = $data;
		if ($name !== null) {
			$this->name = $name;
			$this->setAttr('data-name', $name);
		}
	}

	public function set($field, $value) {
		if (is_array($this->data)) {
			$this->data[$field] = $value;
		} else {
			$this->data->set($field, $value);
		}

		return $this;
	}

	public function get($field, $default = null) {
		if (is_array($this->data)) {
			return isset($this->data[$field]) ? $this->data[$field] : $default;
		} else {
			return $this->data->get($field, $default);
		}
	}

	public function getData() {
	    return $this->data;
    }

	public function add($field, $type, $validator = null) {
		$this->fields[$field] = $type;

		if ($validator !== null) {
			$this->fieldValidators[$field] = $validator;
		}
	}

	public function addValidator($validator) {
		$this->validators[] = $validator;
		return $this;
	}

	public function setAttr($attr, $value) {
		$this->attr[$attr] = $value;
	}

	public function getAttr($attr, $default = null) {
		return isset($this->attr[$attr]) ? $this->attr[$attr] : $default;
	}

	public function setAction($action) {
		if (Config::get()['session_add_id_in_urls'] && SID !== '') {
			if (strpos('?', $action) !== false) {
				$action .= '&';
			} else {
				$action .= '?';
			}
			$action .= htmlspecialchars(SID);
		}

		$this->attr['action'] = $action;
	}

	public function isValid() {
		return count($this->messages['error']) == 0;
	}

	public function bind($params) {

		// CSRF check
		if (!isset($params['_csrf_token']) || !$this->isCsrfTokenValid($params['_csrf_token'])) {
			if (Config::get()['debug']) {
				$this->addError('_csrf_token', "Erreur de token CSRF: ".join(', ', $this->getCsrfTokens())." est attendu et ".(isset($params['_csrf_token']) ? $params['_csrf_token'] : "rien")." a été fourni.");
			} else {
				$this->addError('_csrf_token', "Votre session a expiré, merci de raffraîchir la page.");
			}
			return;
		}

		foreach ($this->fields as $field => $type) {
			$this->set($field, isset($params[$field]) ? cast($params[$field], $type) : null);
		}

		foreach ($this->fieldValidators as $field => $validator) {
			$error = $validator($this->get($field));

			if (isset($error) && is_string($error)) { // a string, the error is added to the current field
				$this->addError($field, $error);
			} elseif (is_array($error)) { // an array, the keys are the fields on which the errors must be placed
				$this->messages['error'] = $error + $this->messages['error'];
			}
		}

		foreach ($this->validators as $validator) {
			$error = $validator($this);

			if (isset($error) && is_string($error)) { // a string, the error is added to the current field
				$this->addError(null, $error);
			} elseif (is_array($error)) { // an array, the keys are the fields on which the errors must be placed
				$this->messages['error'] = $error + $this->messages['error'];
			}
		}

	}

    public function generateCsrfToken() {
        $config = Config::get();
        $token = md5(rand());
        $csrf_tokens = Session::get('_csrf_tokens');

        if (empty($csrf_tokens)) {
            $csrf_tokens = [$token];
        } else {
            $csrf_tokens[] = $token;
        }

        if (count($csrf_tokens) > $config['csrf_token_validity']) {
            array_shift($csrf_tokens);
        }

        Session::set('_csrf_tokens', $csrf_tokens);

        return $token;
    }

    public function getCsrfTokens() {
        $csrf_tokens =  Session::get('_csrf_tokens');
        return is_array($csrf_tokens) ? $csrf_tokens : [];
    }

    public function isCsrfTokenValid($token) {
        return in_array($token, $this->getCsrfTokens());
    }

    public function printCsrfToken() {
        echo '<input type="hidden" name="_csrf_token" value="'.$this->generateCsrfToken().'">';
    }

	public function printMessages() {
		foreach ($this->messages as $type => $messages) {
			foreach ($messages as $field => $message) {
				echo '<label for="'.$field.'" class="'.$type.'">'.$message.'</label>'."\n";
			}
		}
	}

    public function hasMessages() {
        return count($this->messages['error']) > 0 || count($this->messages['warning']) > 0 || count($this->messages['success']) > 0;
    }

	public function hasErrors() {
		return count($this->messages['error']) > 0;
	}

	public function addError($field, $message) {
		$this->addMessage('error', $field, $message);
	}

	public function addSuccess($field, $message) {
		$this->addMessage('success', $field, $message);
	}

	public function addMessage($type, $field, $message) {
		if ($field === null) {
			$this->messages[$type][] = $message;
		} else {
			$this->messages[$type][$field] = $message;
		}
	}

	public function open($class = null, $additionnal_attr = []) {

        $this->setAttr('method', "post");

		if ($class !== null) {
			$this->setAttr('class', $this->getAttr('class').' '.$class);
		}

		if (!empty($additionnal_attr)) {
			foreach ($additionnal_attr as $key => $value) {
				$this->attr[$key] = $value;
			}
		}

		if (Config::get()['session_add_id_in_urls'] && !isset($this->attr['action'])) {
			$this->setAction(''); // automatically add PHPSESSID
		}


		echo '<form ';
		foreach ($this->attr as $attr => $value) {
			echo $attr.'="'.$value.'" ';
		}
		echo '>';

		$this->printMessages();
		$this->printCsrfToken();
	}

	public function close() {
		echo '</form>';
	}

} 