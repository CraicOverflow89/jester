<?php

namespace Jester\Components;

abstract class Model {

	abstract public function create();

	public function getByID($id): Model {
		// TODO: lookup $table using $id
	}

}