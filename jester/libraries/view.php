<?php

namespace Jester\Libraries;

class View {

	public static function content($view, $data = []) {
		extract($data);
		ob_start();
		include 'app/views/' . $view . '.php';
		return ob_get_clean();
	}

	public static function include($view, $data = []) {
		extract($data);
		ob_start();
		include 'app/views/' . $view . '.php';
		print ob_get_clean();
	}

}