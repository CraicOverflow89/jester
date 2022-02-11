<?php

namespace Jester\Libraries;

/**
 * Determines if a string is valid JSON
 *
 * @param string $value the string to check
 * @return bool
 */
function isJSON($value): bool {
	if(!is_string($value)) return false;
	json_decode($value);
	return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * ArrayList
 *
 * @author craicoverflow89
 */
final class ArrayList {

	/** @var array $data */
	private $data;

	/**
	 * Constructs an ArrayList
	 *
	 * @param mixed $valueList initial value(s)
	 * @return ArrayList
	 */
	public function __construct(...$valueList) {
		$this->data = $valueList;
	}

	/**
	 * Adds an element
	 *
	 * @param mixed $value The element to add
	 * @return ArrayList
	 */
	public function add($value): ArrayList {
		array_push($this->data, $value);
		return $this;
	}

	/**
	 * Returns the count of elements
	 *
	 * @return int
	 */
	public function size(): int {
		return count($this->data);
	}

	/**
	 * Returns the ArrayList as an array
	 *
	 * @return array<mixed>
	 */
	public function toArray(): array {
		return $this->data;
	}

	/**
	 * Returns string representation
	 *
	 * @return string
	 */
	public function __toString(): string {
		$result = [];
		foreach($this->data as $element) {
			$result[] = $element;
		}
		return '[' . implode(', ', $result) . ']';
	}

}

/**
 * Creates an ArrayList
 *
 * @param mixed $valueList value(s)
 * @return ArrayList
 */
function listOf(...$valueList): ArrayList {
	return new ArrayList(...$valueList);
}

/**
 * Pair
 *
 * @author craicoverflow89
 */
final class Pair {

	/** @var mixed $first */
	public $first;

	/** @var mixed $second */
	public $second;

	/**
	 * Constructs a Pair
	 *
	 * @param mixed $first value
	 * @param mixed $second value
	 * @return Pair
	 */
	public function __construct($first, $second) {
		$this->first = $first;
		$this->second = $second;
	}

}

/**
 * Creates a Pair
 *
 * @param mixed $first value
 * @param mixed $second value
 * @return Pair
 */
function Pair($first, $second): Pair {
	return new Pair($first, $second);
}

/**
 * Stream
 *
 * @author craicoverflow89
 */
class Stream extends \ArrayObject {

	/** @var array $data */
	private $data;

	/**
	 * Constructs a Stream
	 *
	 * @param array $data pairs
	 * @throws Exception if $data is not an associative array of key/value pairs
	 * @return Stream
	 */
	public function __construct(array $data = array()) {

		// Invalid Array
		//if(array_keys($data) === range(0, count($data) - 1)) throw new \Exception('Data must contains key/value pairs.');

		// Create Stream
		$this->data = $data;
	}

	/**
	 * Adds a pair to the stream
	 *
	 * @param string $key the key
	 * @param mixed $value the value
	 * @return Stream
	 */
	public function add(string $key, $value): Stream {

		// Append Pair
		$this->data[$key] = $value;

		// Return Stream
		return $this;
	}

	/**
	 * Determines if all pairs match a predicate
	 *
	 * @param Callable $logic ($k: String, $v: Any)->Boolean
	 * @return bool
	 * @throws Exception if $logic does not return boolean
	 */
	public function all(Callable $logic): bool {

		// Iterate Pairs
		foreach($this->data as $k => $v) {

			// Invoke Predicate
			$pairMatch = $logic($k, $v);

			// Invalid Return
			if(!is_bool($pairMatch)) throw new \Exception('Logic must return boolean.');

			// Match Failure
			if(!$pairMatch) return false;
		}

		// Match Success
		return true;
	}

	/**
	 * Determines if any pairs match a predicate
	 *
	 * @param Callable $logic ($k: String, $v: Any)->Boolean | null
	 * @return bool
	 * @throws Exception if $logic does not return boolean
	 */
	public function any(Callable $logic = null): bool {

		// No Logic
		if($logic == null) return !!count($this->data);

		// Iterate Pairs
		foreach($this->data as $k => $v) {

			// Invoke Predicate
			$pairMatch = $logic($k, $v);

			// Invalid Return
			if(!is_bool($pairMatch)) throw new \Exception('Logic must return boolean.');

			// Match Success
			if($pairMatch) return true;
		}

		// Match Failure
		return false;
	}

	/**
	 * Creates a generator for the stream
	 *
	 * @return array [isDone: Boolean, next: Any]
	 */
	public function asIterable(): array {

		// Instantiate Position
		$pos = 0;

		// Return Proxy
		return [
			'isDone' => function() use ($pos) {

				// Return Completion
				return $pos > count($this->data) - 1;
			},
			'next' => function() use ($pos) {

				// Invalid Position
				if($pos > count($this->data)) throw new \Exception('Generator has reached end of stream.');

				// Increment Position
				$pos ++;

				// Return Data
				return $this->data[$pos];
			}
		];
	}

	/**
	 * Creates an array of streams of max size
	 *
	 * @param Int $size maximum Stream size
	 * @return array<Stream>
	 * @throws Exception if $size is fewer than one
	 */
	public function chunked(Int $size): array {

		// Validate Size
		if($size < 1) throw new \Exception('Size must be at least one.');

		// Define Result
		$result = [[]];

		// Iterate Pairs
		$pos = 0;
		foreach($this->data as $k => $v) {

			// Next Chunk
			if(count($result[$pos]) == $size) {
				$pos ++;
				$result[$pos] = [];
			}

			// Append Pair
			$result[$pos][$k] = $v;
		}

		// Return Result
		return $result;
	}

	/**
	 * Filters pairs that match predicate
	 *
	 * @param Callable $logic ($k: String, $v: Any)->Boolean
	 * @return Stream
	 * @throws Exception if $logic does not return boolean
	 */
	public function filter(Callable $logic): Stream {

		// Define Result
		$result = [];

		// Iterate Pairs
		foreach($this->data as $k => $v) {

			// Invoke Predicate
			$pairInclude = $logic($k, $v);

			// Invalid Return
			if(!is_bool($pairInclude)) throw new \Exception('Logic must return boolean.');

			// Include Pair
			if($pairInclude) $result[$k] = $v;
		}

		// Update Data
		$this->data = $result;

		// Return Stream
		return $this;
	}

	/**
	 * Finds the first pair to match a predicate
	 *
	 * @param Callable $logic ($k: String, $v: Any)->Boolean
	 * @return mixed
	 * @throws Exception if $logic does not return boolean
	 */
	public function first(Callable $logic): mixed {

		// No Logic
		if($logic == null) return !!count($this->data);

		// Iterate Pairs
		foreach($this->data as $k => $v) {

			// Invoke Predicate
			$pairMatch = $logic($k, $v);

			// Invalid Return
			if(!is_bool($pairMatch)) throw new \Exception('Logic must return boolean.');

			// Match Success
			if($pairMatch) return $v;
		}

		// Match Failure
		return null;
	}

	/**
	 * Folds the stream into a single value
	 *
	 * @param mixed $initial value to start
	 * @param Callable $logic ($result: Any, $k: String, $v: Any)->Any
	 * @return mixed
	 */
	public function fold(mixed $initial, Callable $logic): mixed {

		// Define Result
		$result = $initial;

		// Iterate Pairs
		foreach($this->data as $k => $v) $result = $logic($result, $k, $v);

		// Return Result
		return $result;
	}

	/**
	 * Performs logic against pairs
	 *
	 * @param Callable $logic ($k: String, $v: Any)
	 */
	public function forEach(Callable $logic): void {

		// Iterate Pairs
		foreach($this->data as $k => $v) $logic($k, $v);
	}

	/**
	 * Maps pairs on logic
	 *
	 * @param Callable $logic ($k: String, $v: Any)->Any
	 * @return Stream
	 */
	public function map(Callable $logic): Stream {

		// Iterate Pairs
		foreach($this->data as $k => $v) $this->data[$k] = $logic($k, $v);

		// Return Stream
		return $this;
	}

	/**
	 * Determines if no pairs match a predicate
	 *
	 * @param Callable $logic ($k: String, $v: Any)->Boolean
	 * @return bool
	 * @throws Exception if $logic does not return boolean
	 */
	public function none(Callable $logic): bool {

		// Iterate Pairs
		foreach($this->data as $k => $v) {

			// Invoke Predicate
			$pairMatch = $logic($k, $v);

			// Invalid Return
			if(!is_bool($pairMatch)) throw new \Exception('Logic must return boolean.');

			// Match Failure
			if($pairMatch) return false;
		}

		// Match Success
		return true;
	}

	/**
	 * Performs logic against pairs and returns stream
	 *
	 * @param Callable $logic ($k: string, $v: mixed)
	 * @return Stream
	 */
	public function onEach(Callable $logic): Stream {

		// Iterate Pairs
		foreach($this->data as $k => $v) $logic($k, $v);

		// Return Stream
		return $this;
	}

	/**
	 * Partitions data into two streams
	 *
	 * @param Callable $logic ($k: string, $v: mixed)->bool
	 * @return Pair
	 */
	public function partition(Callable $logic): Pair {

		// Define Results
		$result = [
			'first' => [],
			'second' => []
		];

		// Iterate Pairs
		foreach($this->data as $k => $v) {

			// Invoke Predicate
			$pairPartition = $logic($k, $v);

			// Invalid Return
			if(!is_bool($pairPartition)) throw new \Exception('Logic must return boolean.');

			// Include Pair
			$result[$pairPartition === true ? 'first' : 'second'][$k] = $v;
		}

		// Return Result
		return Pair($result['first'], $result['second']);
	}

	/**
	 * Reduces the stream into a single value
	 *
	 * @param Callable $logic ($result: mixed, $k: string, $v: mixed)->mixed
	 * @return mixed
	 */
	public function reduce(Callable $logic): mixed {

		// Define Result
		$result = null;

		// Iterate Pairs
		foreach($this->data as $k => $v) $result = $logic($result, $k, $v);

		// Return Result
		return $result;
	}

	/**
	 * Rejects pairs that do not match predicate
	 *
	 * @param Callable $logic ($k: string, $v: any)->bool
	 * @return Stream
	 * @throws Exception if $logic does not return boolean
	 */
	public function reject(Callable $logic): Stream {

		// Define Result
		$result = [];

		// Iterate Pairs
		foreach($this->data as $k => $v) {

			// Invoke Predicate
			$pairInclude = $logic($k, $v);

			// Invalid Return
			if(!is_bool($pairInclude)) throw new \Exception('Logic must return boolean.');

			// Include Pair
			if(!$pairInclude) $result[$k] = $v;
		}

		// Update Data
		$this->data = $result;

		// Return Stream
		return $this;
	}

	/**
	 * Converts the stream to an array
	 *
	 * @return array<mixed>
	 */
	public function toArray(): array {
		return $this->data;
	}

	/**
	 * Converts the stream to JSON
	 *
	 * @return string
	 */
	public function toJSON(): string {
		return json_encode($this->data);
	}

	/**
	 * Converts the stream to a map
	 *
	 * @return array<string, mixed>
	 */
	public function toMap(): array {
		return $this->data;
	}

}

/**
 * Creates a Stream
 *
 * @param mixed $data associative array of key/value pairs or JSON string
 * @throws Exception if $data is not an associative array of key/value pairs or valid JSON
 * @return Stream
 */
function Stream($data = array()): Stream {

	// Decode JSON
	if(isJSON($data)) $data = json_decode($data, true);

	// Return Stream
	return new Stream($data);
}