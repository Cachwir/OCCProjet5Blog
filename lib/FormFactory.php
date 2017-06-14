<?php

namespace lib;

class FormFactory
{
    public static function createForm($data = [], $name = null) {
        $Form = new Form($data, $name);
        $Form->setAction(Config::get()['ajaxController']);
        return $Form;
    }

    /**
     * @param ORM|array $target The target array or entity which is going to be fed
     * @param array     $attributes  The form attributes
     * @param $fields
     * @param null $form_validator
     * @return Form
     */
    public static function createGenericForm($target, $fields, $attributes = [], $form_validator = null)
    {
        $Form = static::createForm($target);

        foreach ($attributes as $name => $value) {
            $Form->setAttr($name, $value);
        }
        foreach ($fields as $field) {
            $Form->add($field[0], $field[1], isset($field[2]) ? $field[2] : null);
        }
        if (is_callable($form_validator)) {
            $Form->addValidator($form_validator);
        }

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