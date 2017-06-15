<?php

namespace lib;

use Twig_Environment;
use Twig_Loader_Filesystem;

class Request {

    protected static $params;
    protected static $method;
    protected static $ip;
    protected static $user_agent;

	public static function init() {
        self::parseRequest();
	}

    public static function parseRequest() {
        self::$params        = $_POST + $_GET;
        self::$method        = strtolower($_SERVER['REQUEST_METHOD']);
        self::$ip            = $_SERVER['REMOTE_ADDR'];
        self::$user_agent    = null;

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            self::$user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
    }

	public static function get($field, $default = null) {
	    return isset(self::$params[$field]) ? self::$params[$field] : $default;
    }
    public static function set($field, $value) {
        self::$params[$field] = $value;
    }

    public static function getParams() {
        return self::$params;
    }
    public static function setParams($params) {
        self::$params = $params;
    }

    public static function getMethod() {
        return self::$method;
    }
    public static function setMethod($method) {
        self::$method = $method;
    }

    public static function getIp() {
        return self::$ip;
    }

    public static function getUserAgent() {
        return self::$user_agent;
    }

    public static function isAjax() {
        return self::getMethod() == 'post' && self::get('mode') == 'ajax';
    }
}


