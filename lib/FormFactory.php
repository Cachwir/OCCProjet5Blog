<?php

namespace lib;

class FormFactory
{
    public static function createForm($data = [], $name = null) {
        $Form = new Form($data, $name);
        $Form->setAction(Config::get()['ajaxController']);
        return $Form;
    }

    public function setCaptchaKey($form_name, $key) {
        $keys = Session::get('CAPTCHA_KEYS', []);
        $keys[$form_name][] = $key;
        if (count($keys[$form_name]) > Config::get()['csrf_token_validity']) { // because the infrastructure may perform additional GET requests, we need to store the last N captcha codes
            array_shift($keys[$form_name]);
        }
        Session::set('CAPTCHA_KEYS', $keys);
    }

    public function isCaptchaKeyValid($form_name, $key) {
        $keys = Session::get('CAPTCHA_KEYS', []);
        return isset($keys[$form_name]) && in_array($key, $keys[$form_name]);
    }
} 