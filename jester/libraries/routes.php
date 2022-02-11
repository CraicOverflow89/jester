<?php

namespace Jester\Libraries;

class Routes {

	private static $routeData = [];

	public static function add($path, $method, $event) {
		self::$routeData[] = [
			'path' => $path,
			'method' => $method,
			'controller' => $event[0],
			'function' => $event[1],
			'hasVariables' => str_contains($path, '{') && str_contains($path, '}')
		];
		// NOTE: path needs to be parsed so that elements like {this} are taken into variables
		//       should replace two contains calls with single regex match
	}

	public static function list() {
		return self::$routeData;
	}

	public static function match($route, $uri) {

		// Route Parts
		$variableData = [];
		$routeParts = explode('/', $route);
		$uriParts = explode('/', $uri);

		// Iterate Parts
		for($x = 0; $x < count($routeParts); $x ++) {

			// URI End
			if(count($uriParts) <= $x) return null;

			// Store Variable
			if(preg_match('/^{.+}$/', $routeParts[$x])) {
				$variableData[] = $uriParts[$x];
			}

			// Physical Match
			else {
				if($routeParts[$x] !== $uriParts[$x]) return null;
			}
		}

		// Successful Match
		return $variableData;
	}

}