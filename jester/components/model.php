<?php

namespace Jester\Components;

use \Jester\Libraries\Database;

abstract class Model implements \JsonSerializable {

	protected static $tableName = '';
	protected static $fieldList = [];
	protected $entityID = null;
	protected $entityData = [];

	public function __construct(int $id, array $data) {
		$this->entityID = $id;
		$this->entityData = $data;
	}

	final public function __toString(): string {
		return 'Model<' . static::$tableName . ', id ' . $this->entityID . ', ' . json_encode($this->entityData) . '>';
	}

	final public static function create(array $data): int {
		return Database::insert('INSERT INTO ' . static::$tableName . ' ('
			. implode(', ', array_keys(static::$fieldList))
			. ') VALUES ('
			. implode(', ', array_map(fn($it) => ':' . $it, array_keys(static::$fieldList)))
			. ')',
		$data);
	}

	final public static function createTable(): void {
		Database::invoke('CREATE TABLE ' . static::$tableName . '('
			. 'id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, '
			. implode(', ', array_map(fn($it) => $it . ' ' . static::$fieldList[$it], array_keys(static::$fieldList)))
			. ')'
		);
	}

	final public function entityData(): array {
		return $this->entityData;
	}

	final public function entityID(): int {
		return $this->entityID;
	}

	final public static function getAll(): array {
		return array_map(function($data) {
			return self::getEntity($data['id'], $data);
		}, Database::select('SELECT id, '
			. implode(', ', array_keys(static::$fieldList))
			. ' FROM ' . static::$tableName
		));
	}

	final public static function getByID(int $id): Model|null {
		$data = Database::select('SELECT id, '
			. implode(', ', array_keys(static::$fieldList))
			. ' FROM ' . static::$tableName
			. ' WHERE id = :id',
		[
			'id' => $id,
		]);
		if(!count($data)) {
			return null;
		}
		return self::getEntity($id, $data[0]);
	}

	private static function getEntity($id, $data): Model {
		$result = [];
		foreach(static::$fieldList as $field => $type) {
			$result[$field] = $data[$field];
		}
		return static::init($id, $result);
	}

	abstract protected static function init(int $id, array $data): Model;

	public function jsonSerialize(): mixed {
		return $this->toArray();
	}

	final public function toArray(): array {
		return array_merge($this->entityData, [
			'id' => $this->entityID,
		]);
	}

	final public function toJSON(): string {
		return json_encode($this->toArray());
	}

	final public function save(): void {

		// Create Entity
		if($this->entityID == 0) {
			$this->entityID = self::create($this->entityData);
		}

		// Existing Entity
		else {
			Database::update('UPDATE ' . static::$tableName . ' SET '
				. implode(', ', array_map(fn($it) => ':' . $it, array_keys(static::$fieldList)))
				. ' WHERE id = :id',
			array_merge($this->entityData, [
				'id' => $this->entityID,
			]));
		}
	}

	final public static function where(string $condition): array {
		return array_map(function($data) {
			return self::getEntity($data['id'], $data);
		}, Database::select('SELECT id, '
			. implode(', ', array_keys(static::$fieldList))
			. ' FROM ' . static::$tableName
			. ' WHERE ' . $condition
		));
	}

}