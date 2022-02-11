<?php

namespace Jester\Libraries;

use \Jester\Libraries\Response;
use function \Jester\Libraries\Stream;
use \Jester\JesterFramework;

class Request {

	private static $uri = null;
	private static $method = null;
	private static $query = null;

	public static function init($uri, $method, $query) {

		// NOTE: this will be '/' for root or '/index' or '/index?name=Jamie' for example
		self::$uri = (function() use ($uri) {
			return substr($uri, 0, strpos($uri, '?') ?: strlen($uri));
		})();

		// NOTE: this will be 'GET' or 'POST' for example
		self::$method = $method;

		// NOTE: this will be '' for nothing or 'name=Jamie' or 'name=Jamie&age=31' for example
		self::$query = (function() use ($query) {

			// Empty Query
			if(strlen($query) < 1) return [];

			// Parsed Query
			return Stream(explode('&', $query)) -> map(
				function($_, $v) {
					return explode('=', $v);
				}
			) -> toArray();
		})();

		// Debug Request
		if($GLOBALS['DEBUG_MODE']) echo 'Request({self::$uri}, {self::$method}, ' . json_encode(self::$query) . ')<br><br>';
	}

	public static function invoke() {

		// Match Route
		$variableMatch = null;
		$routeMatch = Stream(Routes::list()) -> first(function($_, $v) use (&$variableMatch) {

			// Method Mismatch
			if($v['method'] !== self::$method) return false;

			// Trailing Slash
			$uri = self::$uri;
			if(strlen($v['path']) > 1 && substr($uri, -1) == '/') {
				$uri = substr($uri, 0, strlen($uri) - 1);
			}
			// NOTE: might be bettter to say, on route creation
			//       that if the route path doesn't end with a '/'
			//       then append that to the path property for matching here

			// Simple Match
			if(!$v['hasVariables']) return $v['path'] == $uri;

			// Complex Match
			$variableMatch = Routes::match($v['path'], $uri);
			return $variableMatch !== null;
		});

		// No Match (TEMP)
		if($routeMatch === null) {
			Response::redirect(JesterFramework::getRouteFallback());
		}

		// Execute Route
		call_user_func([$routeMatch['controller'], $routeMatch['function']], ...($variableMatch ?: []));
		// NOTE: this works if controllers have static methods
		//       which would be an added pain for developers using the framework
		//       should controller methods be static or not?
	}

}