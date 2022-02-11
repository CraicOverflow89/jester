<?php

namespace Jester;

// Enable Sessions
session_start();

// Include Logic
function includeDirectory($path) {
	if(!is_dir($path)) return;
	foreach(scandir($path) as $file) {
		if($file !== '.' && $file !== '..') {
			include_once($path . '/' . $file);
		}
	}
}

// Include Framework
includeDirectory('jester/libraries');
includeDirectory('jester/components');

// Include Application
includeDirectory('app/controllers');
includeDirectory('app/models');

// Use Classes
use \Jester\Libraries\Database;
use \Jester\Libraries\Request;

class JesterFramework {

	private static $ROUTE_FALLBACK = null;

	/**
	 * Invokes the main application runtime
	 *
	 * @return void
	 */
	public static function invoke() {

		// Application Settings
		$GLOBALS['DEBUG_MODE'] = false;

		// Database Setup
		Database::connect(...explode('|', file_get_contents('database')));

		// Initiailise Request
		Request::init($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING']);

		// Add Routes (TEMP)
		includeDirectory('app/routes');

		// Execute Request
		Request::invoke();
	}

	/**
	 * Gets the fallback route for when invalid routes are called
	 *
	 * @return string
	 */
	public static function getRouteFallback() {
		return self::$ROUTE_FALLBACK;
	}

	/**
	 * Sets the fallback route for when invalid routes are called
	 *
	 * @param string $route
	 * @return void
	 */
	public static function setRouteFallback($route) {
		self::$ROUTE_FALLBACK = $route;
	}

}