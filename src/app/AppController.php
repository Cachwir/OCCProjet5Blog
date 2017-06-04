<?php

class AppController extends App {

	public static $pages = [
		'home',
	];

	public function homeAction() {

		$params = $this->params;
		$template_params = [];

		return $this->render('home', $template_params);
	}
}