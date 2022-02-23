<?php

namespace Jester\Libraries;

use \Jester\JesterFramework;

class Response {

	public static function json(mixed $value): void {
		header('Content-Type: application/json; charset=utf-8');
		echo(json_encode($value));
		die;
	}

	public static function redirect(string $location): void {
		header('Location: ' . $location);
		die;
	}

	public static function view(string $path, array $data = []): void {
		echo(JesterFramework::$TWIG->render($path . '.twig', $data));
		die;
	}

}