<?php

namespace lib;

class Config {
	public static $config = [];

	public static function get($lang = 'default') {
		return self::$config[$lang];
	}

	public static function set($config, $lang = 'default') {
		return self::$config[$lang] = $config + self::getDefaultConfig($lang);
	}

	public static function getTemplateDir($lang = 'default') {
		return self::$config[$lang]['template_dir'];
	}

	public static function getDefaultLang() {
		return self::get()['default_lang'];
	}

	public static function getDefaultConfig($lang) {
		return (isset(self::$config['default']) ? self::$config['default'] : []) + [
			'debug'                 => false,
			'ajaxController'        => '',
			'default_lang'          => 'fr',

			'csrf_token_validity'   => 6,

			'session_namespace' => '_app',
			'session_add_id_in_urls' => false,
			'forbidden_mail_providers' => [
				'yopmail.com',
				'yopmail.net',
				'jetable.fr',
				'jetable.org',
				'link2mail.net',
				'link2mail.fr',
				'0-mail.com',
				'0-mail.fr',
				'brefmail.com',
				'brefmail.fr',
				'tempinbox.com',
				'tempinbox.fr',
				'lapioste.be',
				'yalnoo.fr',
				'netcovrrier.com',
				'waikia.eu',
				'lyc0s.fr',
			],
		];

	}

}


