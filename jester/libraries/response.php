<?php

namespace Jester\Libraries;

class Response {

	public static function json($value) {
		echo json_encode($value);
		die;
	}

	public static function redirect($location) {
		header('Location: ' . $location);
		die;
	}

	public static function view($page, $data = []) {
		extract($data);
		ob_start();
		include 'app/views/' . $page . '.php';
		echo ob_get_clean();
		die;
	}

}