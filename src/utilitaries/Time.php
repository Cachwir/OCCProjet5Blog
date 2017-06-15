<?php

namespace src\utilitaries;

use lib\App;

/**
 * Class Time
 *
 * This class gives a few tools to manage time. Feel free to fill it with whatever you want.
 */
class Time {

	const MINUTE = 60;
	const HOUR   = 3600;
	const DAY    = 86400;
	const WEEK   = 604800;
	const MONTH  = 2592000;
	const YEAR   = 31536000;

	protected $App;
	
	public function __construct(App $App)
	{
		$this->App = $App;
	}
}