<?php

namespace lib;

class Session {

	public static function init() {
		ensure_session_started();
	}

	public static function get($field, $default = null) {
		$ns = Config::get()['session_namespace'];
		return isset($_SESSION[$ns][$field]) ? $_SESSION[$ns][$field] : $default;
	}

	public static function set($field, $value) {
		$ns = Config::get()['session_namespace'];
		return $_SESSION[$ns][$field] = $value;
	}

    public static function unset($field) {
        $ns = Config::get()['session_namespace'];
        if (isset($_SESSION[$ns][$field])) {
            unset($_SESSION[$ns][$field]);
        }
    }
}