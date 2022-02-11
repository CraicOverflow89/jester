<?php

namespace Jester\Libraries;

class Database {

	private static $connection = null;
	private static $errorMessage = null;

	public static function connect(string $host, string $port, string $database, string $username, string $password): void {
		self::$connection = (function() use ($host, $port, $database, $username, $password) {
			try {
				$db = new \PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database, $username, $password);
				$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				return $db;
			}
			catch(\PDOException $ex) {
				self::$errorMessage = $ex->getMessage();
				throw $ex;
			}
		})();
	}

	private static function execute(string $sql, array $parameterMap = []) {
		$query = self::$connection->prepare($sql);
		$query->execute($parameterMap);
		return $query;
	}

	public static function getErrorMessage(): string {
		return self::$errorMessage;
	}

	public static function insert(string $sql, array $parameterMap = []): int {
		self::execute($sql, $parameterMap);
		return self::$connection->lastInsertId();
	}

	public static function invoke(string $sql, array $parameterMap = []): void {
		self::execute($sql, $parameterMap);
	}

	public static function select(string $sql, array $parameterMap = []) {
		return self::execute($sql, $parameterMap)->fetchAll();
	}

	public static function update(string $sql, array $parameterMap = []): int {
		return self::execute($sql, $parameterMap)->rowCount();
	}

}