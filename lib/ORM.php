<?php

/**
 * Class ORM
 *
 * If you want to manage entities, create one and make it extend this ORM.
 * Then, override its parameters and fill them with this entity's parameters, table's name, etc.
 *
 * You can create your own methods to have more specific db researches.
 *
 * If you have any doubt, you can look at the existing entities and compare it to the entity you wish to create.
 */
abstract class ORM implements \ArrayAccess {

	public static $tableName = "";
	
	public static $fields = [
		'id'    => 'i',
	];

	public static $computedFields = [];

	public static $fieldLabels = [];

	public static $exportedFields = [];

	protected $data = [];

	public static function getFields() {
		return static::$fields + static::$computedFields;
	}

	public static function getDbFields() {
		return static::$fields;
	}

	public static function getComputedFields() {
		return static::$computedFields;
	}

	public static function findById($id) {
		return static::findOneByWhere("id = :id", [':id' => $id]);
	}

	/**
	 * @return ORM[]
	 */
	public static function findByIds($ids) {
		return static::findByWhere("id in (:ids)", [':ids' => $ids]);
	}

	public static function findByWhere($where, $params = []) {
		$rows = PDOWrapper::get()->fetchAll("select * from ".static::$tableName." where $where", $params);

		$Entities = [];

		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$Entities[] = static::createObjectFromRow($row);
			}
		}

		return $Entities;
	}

	public static function findOneByWhere($where, $params = []) {
		$row = PDOWrapper::get()->fetch("select * from ".static::$tableName." where $where limit 1", $params);
		return static::createObjectFromRow($row);
	}

	protected static function createObjectFromRow($row) {
		if (is_array($row)) {
			$className = get_called_class();
			$Entity = new $className();

			foreach ($row as $field => $value) {
				$Entity->set($field, $value);
			}

			return $Entity;
		} else {
			return null;
		}
	}

	/**
	 * @return ORM[]
	 */
	public static function findAll() {
		return static::findByWhere("1");
	}

	public static function getFieldType($field) {
		if (!array_key_exists($field, static::getFields())) {
			return null;
		}

		return static::getFields()[$field];
	}


	public function set($field, $value) {
		$method = camelize("set_$field");
		if (method_exists($this, $method)) {
			return call_user_func([$this, $method], $value);
		}

		if (!array_key_exists($field, static::getFields())) {
			throw new \Exception("Invalid field: $field");
		}

		$this->data[$field] = cast($value, static::getFieldType($field));

		return $this;
	}

	public function get($field, $default = null) {
		$method = camelize("get_$field");
		if (method_exists($this, $method)) {
			return call_user_func([$this, $method]);
		}

		if (!array_key_exists($field, static::getFields())) {
			throw new \Exception("Invalid field: $field");
		}

		return isset($this->data[$field]) ? $this->data[$field] : $default;
	}

	public function getData($fields = null) {
		if ($fields == null) {
			return $this->data;
		} else {
			return array_intersect_key($this->data, array_combine($fields, array_fill(0, count($fields), 1)));
		}
	}

	public function getDbData() {
		$data = [];
		foreach (static::getDbFields() as $field => $type) {
			if (array_key_exists($field, $this->data)) {
				$data[$field] = uncast($this->data[$field], static::getFieldType($field));
			}
		}
		return $data;
	}

	public function save() {
		$pdo = PDOWrapper::get();

		$data = $this->getDbData();

		if (count($data) > 0) {
			$pdo->insert(static::$tableName, $data, true);
		}

		if (!isset($this->data['id'])) {
			$this->data['id'] = (int)$pdo->lastInsertId();
		}
	}

	/**
	 * Same as save(), but only if the object already exists in the DB
	 */
	public function update() {
		if ($this->get('id') !== null) {
			$this->save();
		}
	}

	public function delete() {
		if ($this->get('id') !== null) {
			$db = PDOWrapper::get();

			$stmt = $db->prepare("DELETE FROM `".static::$tableName."` WHERE `id`=:id");
			$stmt->bindValue(':id', $this->get('id'), \PDO::PARAM_INT);

			$res = $stmt->execute();

			if ($res) {
				$this->set("id", null);
			}

			return $res;
		}
	}

	public function isHashed($mail_field) {
		return is_sha1($this->get($mail_field));
	}

	public function hashField($mail_field) {
		$this->set($mail_field, sha1($this->get($mail_field)));
	}

    public function offsetExists ($offset) {
	    return array_key_exists($offset, static::$fields);
    }
    public function offsetGet ($offset) {
	    return $this->get($offset);
    }
    public function offsetSet ($offset ,$value) {
	    return $this->set($offset, $value);
    }
    public function offsetUnset ($offset) {
	    return $this->set($offset, null);
    }
}


