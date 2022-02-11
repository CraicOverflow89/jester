<?php

namespace Jester\Libraries;

class Database {

	private static $connection = null;
	private static $errorMessage = null;

	public static function connect($host, $database, $username, $password) {
		self::$connection = (function() use ($host, $database, $username, $password) {
			try {
				$db = new \PDO('mysql:host=$host;dbname=$database', $username, $password);
				$db -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				return $db;
			}
			catch(\PDOException $ex) {
				self::$errorMessage = $ex -> getMessage();
				return null;
			}
		})();
	}

	private static function execute($sql, $parameterMap = []) {
		$query = self::$connection -> prepare($sql);
		$query -> execute($parameterMap);
		return $query;
	}

	public static function getErrorMessage() {
		return self::$errorMessage;
	}

	public static function insert($sql, $parameterMap = []) {
		self::execute($sql, $parameterMap);
		return self::$connection -> lastInsertId();
	}

	public static function select($sql, $parameterMap = []) {
		return self::execute($sql, $parameterMap) -> fetchAll();
	}

	public static function update($sql, $parameterMap = []) {
		self::execute($sql, $parameterMap) -> rowCount();
	}

}