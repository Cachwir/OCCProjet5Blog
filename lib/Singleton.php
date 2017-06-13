<?php

namespace lib;

trait Singleton
{
    private static $Instance = null;

    public static function getInstance($params = []) {
        if (!self::$Instance instanceof static) {
            $Reflection = new \ReflectionClass(get_called_class());
            $Instance = $Reflection->newInstanceArgs($params);
            self::$Instance = $Instance;
        }
        return self::$Instance;
    }
}