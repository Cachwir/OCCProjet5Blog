<?php

namespace lib;

class Session {

    protected static $session;

	public static function init() {
		ensure_session_started();
		self::$session = &$_SESSION[Config::get()['session_namespace']];
	}

	public static function get($field, $default = null) {
		return isset(self::$session[$field]) ? self::$session[$field] : $default;
	}

	public static function set($field, $value) {
		return self::$session[$field] = $value;
	}

    public static function unset($field) {
        if (isset(self::$session[$field])) {
            unset(self::$session[$field]);
        }
    }

    public static function initFlashBag() {
	    if (!isset(self::$session["flash"])) {
            self::$session["flash"] = [];
        }
    }

    public static function addFlashMessage($type, $message) {
        self::$session["flash"][$type][] = $message;
    }

    public static function getFlashMessages($type) {
        if (isset(self::$session["flash"][$type])) {
            $flash_messages = self::$session["flash"][$type];
            unset(self::$session["flash"][$type]);
        } else {
            $flash_messages = [];
        }
	    return $flash_messages;
    }
}